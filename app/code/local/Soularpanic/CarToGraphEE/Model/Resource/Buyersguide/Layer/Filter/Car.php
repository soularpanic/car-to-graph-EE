<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Car
    extends Mage_Core_Model_Resource_Db_Abstract {

    protected $_directFitAction;
    protected $_noFitAction;
    protected $_directFitBundleTargets;

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init('cartographee/car', 'entity_id');
    }


    public function applyFilterToCollection($filter, $value) {
        Mage::log('applying car filter in resource!', null, 'trs_guide.log');
        $collection = $filter->getLayer()->getProductCollection();
        $dfBundleTargets = $this->getDirectFitBundleTargets();
        Mage::log('resource direct fit bundle targets: '.print_r($dfBundleTargets, true), null, 'trs_guide.log');

        $directFitSelect = $collection->getSelect();
        $originalSelect = clone $directFitSelect;

        $carAlias = 'car';
        $linkAlias = Mage::helper('cartographee/buyersguide_action')->getCarLinkTableAlias();
        $linkTable = $this->getTable('cartographee/linkcarproduct');
        $attributeSetAlias = 'eas';
        if ($dfBundleTargets) {
            $bundleHelper = Mage::helper('cartographee/buyersguide_bundle');
            $directFitSelect
                ->join(['package_options' => $this->getTable('bundle/selection')],
                    "package_options.parent_product_id = e.entity_id",
                    [])
//                ->join(['f' => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
//                    "f.entity_id = package_options.product_id",
//                    [])
//                ->join([$attributeSetAlias => 'eav_attribute_set'],
//                    "$attributeSetAlias.attribute_set_id = f.attribute_set_id",
//                    [])
                ->group('e.entity_id');
            foreach ($dfBundleTargets as $_dfBundleTarget) {
                //$targetsStmt = "('".implode("', '", $dfBundleTargets)."')";
                $dfBundleTarget = strtolower(str_replace(' ', '_', $_dfBundleTarget));
                $carAlias = "car_$dfBundleTarget";
                $linkAlias = "carlink_$dfBundleTarget";
                $optionProductAlias = "option_product_$dfBundleTarget";
                $fitOptionAlias = "fit_option_$dfBundleTarget";
                $attributeSetAlias = "attribute_set_$dfBundleTarget";
                $f = "f_$dfBundleTarget";

                $sqlString = "(select
                    f.entity_id
                    ,f.sku
                    ,links.option, links.type
                    ,cars.alt_id
                from
                    catalog_product_flat_1 as f
                    inner join eav_attribute_set as eas
                        on eas.attribute_set_id = f.attribute_set_id
                    inner join cartographee_car_product_links as links
                        on links.product_id = f.entity_id and eas.attribute_set_name = '$_dfBundleTarget'
                    inner join cartographee_cars as cars
                        on cars.entity_id = links.car_id and cars.alt_id = '$value')";

                $directFitSelect
                    ->joinLeft([$f => new Zend_Db_Expr($sqlString)],
                        "$f.entity_id = package_options.product_id",
                        ["preselect_$dfBundleTarget" => "GROUP_CONCAT(DISTINCT $f.sku SEPARATOR ',')"])
                    ->orWhere("$f.sku is not null");


//                $directFitSelect
//                    ->join([$f => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
//                        "$f.entity_id = package_options.product_id",
//                        [])
//                    ->join([$attributeSetAlias => 'eav_attribute_set'],
//                        "$attributeSetAlias.attribute_set_id = $f.attribute_set_id and $attributeSetAlias.attribute_set_name = '$_dfBundleTarget'",
//                        [])
//                    ->join([$linkAlias => $linkTable],
//                        "{$linkAlias}.product_id = $f.entity_id and {$attributeSetAlias}.attribute_set_name = '{$_dfBundleTarget}'",
//                        ["preselect_$dfBundleTarget" => "$f.sku"])
//                    ->join([$carAlias => $this->getMainTable()],
//                        "$carAlias.entity_id = $linkAlias.car_id and $carAlias.alt_id = '$value'",
//                        []);
                //$directFitSelect

//                                                              ->join([$optionProductAlias => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
//                        "$optionProductAlias.entity_id = package_options.product_id",
//                        ["preselect_$dfBundleTarget" => "GROUP_CONCAT(DISTINCT $optionProductAlias.sku SEPARATOR ',')"])
//
//                    ->joinLeft([$fitOptionAlias => $this->getTable('bundle/selection')],
//                        "$fitOptionAlias.product_id = package_options.product_id",
//                        [])
//                    ->joinLeft([$attributeSetAlias => 'eav_attribute_set'],
//                        "$attributeSetAlias.attribute_set_id = $optionProductAlias.attribute_set_id and $attributeSetAlias.attribute_set_name = '$_dfBundleTarget'",
//                        [])
//                    ->joinLeft([$linkAlias => $linkTable],
//                        "$linkAlias.product_id = $fitOptionAlias.parent_product_id",
//                        [])
//                    ->joinLeft([$carAlias => $this->getMainTable()],
//                        "$carAlias.entity_id = $linkAlias.car_id and $carAlias.alt_id = '$value'",
//                        []);
            }
        }
        else {
            $directFitSelect
                ->join([$linkAlias => $linkTable],
                    "{$linkAlias}.product_id = e.entity_id",
                    [])
                ->join([$carAlias => $this->getMainTable()],
                    "{$carAlias}.alt_id = '{$value}' and {$carAlias}.entity_id = {$linkAlias}.car_id",
                    [])
                ->group('e.entity_id');
        }
        Mage::log("DF SQL:\n{$directFitSelect->__toString()}", null, 'trs_guide.log');

        $directFits = $collection->count();
        Mage::log("Direct fits found: [{$directFits}]", null, 'trs_guide.log');
        $collection->clear();

        Mage::log("filter? ".get_class($filter), null, 'trs_guide.log');
        $chain = $filter->getChain();
        Mage::log("chain? ".get_class($chain), null, 'trs_guide.log');
        $state = $filter->getChainState();
        $state['has_direct_fit'] = $directFits;// > 0 ? true : false;
        if (!isset($state['action'])) {
            $state['action'] = $directFits ? $this->getDirectFitAction() : $this->getNoFitAction();
        }

        if ($directFits <= 0) {
            Mage::log("Restoring original select to collection", null, 'trs_guide.log');

            $collection->setSelect($originalSelect);
        }
        else {
            $state['car_id'] = $value;
        }


        Mage::log("in car resource, chain state: [".print_r($state, true)."]", null, 'trs_guide.log');
        $filter->setChainState($state);

        return $this;
    }

    public function setNoFitAction($noFitAction) {
        $this->_noFitAction = $noFitAction;
        return $this;
    }

    public function getNoFitAction() {
        return $this->_noFitAction ?: 'step:nofit';
    }

    public function setDirectFitAction($directFitAction) {
        $this->_directFitAction = $directFitAction;
        return $this;
    }

    public function getDirectFitAction() {
        return $this->_directFitAction ?: 'step:directfit';
    }

    public function setDirectFitBundleTargets($targets) {
        $this->_directFitBundleTargets = $targets;
        return $this;
    }

    public function getDirectFitBundleTargets() {
        return $this->_directFitBundleTargets;
    }
}
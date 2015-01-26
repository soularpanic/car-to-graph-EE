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
        $helper = Mage::helper('cartographee/buyersguide_action');
        if ($value) {
            $collection->getSelect()
                ->join(['car_display' => $this->getTable('cartographee/car')],
                    "car_display.alt_id = '$value'",
                    ['buyers_guide_car_display' => "concat_ws(' ', car_display.year, car_display.make, car_display.model)"]);
        }

        $directFitSelect = $collection->getSelect();
        $originalSelect = clone $directFitSelect;

        $carAlias = 'car';
        $linkAlias = Mage::helper('cartographee/buyersguide_action')->getCarLinkTableAlias();
        $linkTable = $this->getTable('cartographee/linkcarproduct');

        if ($dfBundleTargets) {
            $directFitSelect
                ->join(['package_options' => $this->getTable('bundle/selection')],
                    "package_options.parent_product_id = e.entity_id",
                    [])
                ->group('e.entity_id');
            foreach ($dfBundleTargets as $_dfBundleTarget) {
                $f = $helper->toDirectFitTableAlias($_dfBundleTarget);
//                $dfBundleTarget = strtolower(str_replace(' ', '_', $_dfBundleTarget));
//                $f = "f_$dfBundleTarget";

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
                $preselectAlias = "preselect_$f";
                $directFitSelect
                    ->joinLeft([$f => new Zend_Db_Expr($sqlString)],
                        "$f.entity_id = package_options.product_id",
                        [$preselectAlias => "GROUP_CONCAT(DISTINCT $f.sku SEPARATOR ',')"])
                    ->orWhere("$f.sku is not null")
                    ->having("$preselectAlias is not null");

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
                ->joinLeft(['g' => $this->getTable('catalog/product')],
                    "g.entity_id = {$linkAlias}.preselect_ids",
                    ['preselect' => "g.sku"])
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
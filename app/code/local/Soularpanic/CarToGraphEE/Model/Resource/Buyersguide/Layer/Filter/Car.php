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
        if ($dfBundleTargets) {
            $bundleHelper = Mage::helper('cartographee/buyersguide_bundle');

            $targetsStmt = "('".implode("', '", $dfBundleTargets)."')";
            $directFitSelect
                ->join(['package_options' => $this->getTable('bundle/selection')],
                    "package_options.parent_product_id = e.entity_id",
                    [])
                ->join(['option_products' => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
                    "option_products.entity_id = package_options.product_id",
                    ['preselect' => "GROUP_CONCAT(DISTINCT option_products.sku SEPARATOR ',')"])
                ->join(['fit_options' => $this->getTable('bundle/selection')],
                    "fit_options.product_id = package_options.product_id and fit_options.parent_product_id != package_options.parent_product_id",
                    [])
                ->join(['attribute_sets' => 'eav_attribute_set'],
                    "attribute_sets.attribute_set_id = option_products.attribute_set_id and attribute_sets.attribute_set_name in $targetsStmt",
                    [])
                ->join([$linkAlias => $linkTable],
                    "$linkAlias.product_id = fit_options.parent_product_id",
                    [])
                ->join([$carAlias => $this->getMainTable()],
                    "$carAlias.entity_id = $linkAlias.car_id and $carAlias.alt_id = '$value'",
                    [])
                ->group('e.entity_id');
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
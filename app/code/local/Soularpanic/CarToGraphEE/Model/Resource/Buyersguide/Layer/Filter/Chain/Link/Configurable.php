<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Configurable
    extends Mage_Core_Model_Resource_Db_Abstract {

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init('catalog/product', 'entity_id');
    }


    public function applyFilterToCollection($filter, $action) {
        Mage::log('applying configurable filter in resource!', null, 'trs_guide.log');
        //$collection = $filter->getLayer()->getProductCollection();
        $actionHelper = Mage::helper('cartographee/buyersguide_action');

        $actionHelper->applyActionToCollection($filter, $action, $this);

        return $this;
    }
}
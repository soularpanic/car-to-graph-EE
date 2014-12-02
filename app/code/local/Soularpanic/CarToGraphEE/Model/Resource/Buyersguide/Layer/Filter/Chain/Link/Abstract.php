<?php
abstract class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract
    extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('catalog/product', 'entity_id');
    }

    abstract public function applyFilterToCollection($filter, $option);
}
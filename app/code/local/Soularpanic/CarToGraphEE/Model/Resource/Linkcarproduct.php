<?php
class Soularpanic_CarToGraphEE_Model_Resource_Linkcarproduct
    extends Mage_Core_Model_Resource_Db_Abstract {


    protected function _construct() {
        $this->_init('cartographee/linkcarproduct', 'entity_id');
    }
}
<?php
class Soularpanic_CarToGraphEE_Model_Resource_Car_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected function _construct() {
        $this->_init('cartographee/car');
    }

}
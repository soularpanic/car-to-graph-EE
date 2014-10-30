<?php
class Soularpanic_CarToGraphEE_Model_Resource_Car
    extends Mage_Core_Model_Resource_Db_Abstract {


    protected function _construct() {
        $this->_init('cartographee/car', 'entity_id');
    }


    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if ($field === null && !is_int($value)) {
            $field = 'alt_id';
            $value = strtolower($value);
        }
        return parent::load($object, $value, $field);
    }


    public function save(Mage_Core_Model_Abstract $object)
    {
        $obj = $this->_buildAltId($object);
        return parent::save($obj);
    }


    protected function _buildAltId($car) {
        $altId = Mage::helper('cartographee/car')->getCarAltId($car->getMake(), $car->getModel(), $car->getYear());
        return $car->setAltId($altId);
    }
}
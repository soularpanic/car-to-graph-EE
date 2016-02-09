<?php
class Soularpanic_CarToGraphEE_Block_Widget_Buyersguide_Car
//    extends Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Car
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface {

    protected $_carHelper;


    public function _construct() {
        parent::_construct();
        $this->_carHelper = Mage::helper('cartographee/car');
    }

    public function getAvailableYears() {
        $properties = $this->_carHelper->getPropertiesFromRequest();
        return $this->_carHelper->getFilteredCarProperty('year', $properties, 'DESC');
    }


    public function getAvailableMakes() {
        $properties = $this->_carHelper->getPropertiesFromRequest();
        return $this->_carHelper->getFilteredCarProperty('make', $properties);
    }


    public function getAvailableModels() {
        $properties = $this->_carHelper->getPropertiesFromRequest();
        $models = $this->_carHelper->getFilteredCarProperty('model', $properties);
        Mage::helper('cartographee')->log("available models:\n".print_r($models, true));
        return $models;
    }

}
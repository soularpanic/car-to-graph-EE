<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Car
    extends Mage_Catalog_Block_Layer_Filter_Abstract {


    protected $_carHelper;


    public function _construct() {
        parent::_construct();
        $this->_filterModelName = 'cartographee/buyersguide_layer_filter_car';
        //$this->setTemplate('cartographee/buyersguide/Container.phtml');
        $this->_carHelper = Mage::helper('cartographee/car');
        $this->setContainerClasses(['buyersGuide']);
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
        Mage::log("available models:\n".print_r($models, true), null, 'trs_guide.log');
        return $models;
    }
}
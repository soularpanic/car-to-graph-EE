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
        return $this->_carHelper->getFilteredCarProperty('year', [], 'DESC');
    }


    public function getAvailableMakes() {
        return $this->_carHelper->getFilteredCarProperty('make');
    }


    public function getAvailableModels() {
        return $this->_carHelper->getFilteredCarProperty('model');
    }
}
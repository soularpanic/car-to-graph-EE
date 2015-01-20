<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Step_Generic
    extends Mage_Catalog_Block_Layer_Filter_Abstract {

    protected $_stepConfig;

    public function _construct() {
        $this->_stepConfig = [];
        $this->_filterModelName = 'cartographee/buyersguide_layer_filter_chain_link_configurable';
        parent::_construct();
    }


    protected function _prepareFilter() {
        $this->_filter->setData(array_merge($this->_filter->getData(), ['id' => $this->getNameInLayout()], $this->_data));
    }


    public function getStepId() {
        return $this->getNameInLayout();
    }


    public function setStepConfig($config) {
//        Mage::log("step config: ".print_r($config, true), null, 'trs_guide.log');
        $this->_stepConfig = $config;

        $processed = Mage::helper('cartographee/buyersguide_config')->processStepConfigArray($config);
        $this->_data = array_merge($this->_data, $processed);
        if ($this->_data['model']) {
            Mage::log("overwriting filter model from {$this->_filterModelName} to {$this->_data['model']}", null, 'trs_guide.log');
            $this->_filterModelName = $this->_data['model'];
        }
    }

}
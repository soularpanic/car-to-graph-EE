<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Buyersguide
    extends Mage_Catalog_Block_Layer_Filter_Abstract {


    public function _construct() {
        parent::_construct();
        $this->_filterModelName = 'cartographee/buyersguide_layer_filter_chain';
        $this->setContainerClasses(['buyersGuide']);
    }


    protected function _prepareFilter() {
        $filterChildren = [
            'buyersguide_toolbar_supplemental_precar',
            'buyersguide_car_filter',
            'buyersguide_toolbar_supplemental_postcar',
            'buyersguide_steps'
        ];

        foreach ($filterChildren as $filterChild) {
            $block = $this->getLayout()->getBlock($filterChild);
            if ($block) {
                $this->_filter->addLink($block);
            }
        }
        return $this;
    }

    public function addContainerClass($containerClass) {
        $this->setContainerClasses(array_merge($this->getContainerClasses(), [$containerClass]));
    }

    public function getContainerClassString() {
        $containerClasses = $this->getContainerClasses();
        return implode(' ', $containerClasses);
    }

    public function getActionState() {

        if ($this->_filter) {
            $logger = Mage::helper('cartographee');
            $logger->log("_filter chain state: [".print_r($this->_filter->getChainState(), true).']');
            $logger->log("this chain state: [".print_r($this->getChainState(), true).']');
            return $this->_filter->getChainState();
        }
        return 'no chain state';
    }
}
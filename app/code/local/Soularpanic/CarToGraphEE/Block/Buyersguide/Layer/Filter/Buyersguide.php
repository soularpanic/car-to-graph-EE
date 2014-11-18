<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Buyersguide
    extends Mage_Catalog_Block_Layer_Filter_Abstract {


    public function _construct() {
        parent::_construct();
        $this->_filterModelName = 'cartographee/buyersguide_layer_filter_chain';
        $this->setContainerClasses(['buyersGuide']);
    }


    protected function _prepareFilter() {
        $filterChildren = ['buyersguide_car_filter', 'buyersguide_toolbar_supplemental', 'buyersguide_steps'];
        //$filterChildren = ['buyersguide_car_filter'];
        foreach ($filterChildren as $filterChild) {
            $block = $this->getLayout()->getBlock($filterChild);
            //$block = $this->getChild($filterChild);
            if ($block) {
                //$this->_filter->addDelegate($block);
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
            return $this->_filter->getChainState();
        }
        return 'fuck you';
        //return $this->_filter->getChainState();
    }
}
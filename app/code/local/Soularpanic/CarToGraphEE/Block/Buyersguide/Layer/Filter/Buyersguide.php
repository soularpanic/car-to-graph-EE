<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Buyersguide
    extends Mage_Catalog_Block_Layer_Filter_Abstract {


    public function __construct() {
        parent::__construct();
        $this->_filterModelName = 'cartographee/buyersguide_layer_filter_delegate';
        $this->setContainerClasses(['buyersGuide']);
    }


    protected function _prepareFilter() {
        $filterChildren = ['buyersguide_steps', 'buyersguide_car_filter'];
        foreach ($filterChildren as $filterChild) {
            $block = $this->getLayout()->getBlock($filterChild);
            if ($block) {
                $this->_filter->addDelegate($block);
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
}
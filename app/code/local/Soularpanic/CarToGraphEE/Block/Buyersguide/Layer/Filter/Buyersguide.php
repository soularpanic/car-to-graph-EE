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
//        foreach ($this->getSortedChildBlocks() as $_name => $_block) {
//            Mage::log("checking '$_name'...", null, 'trs_guide.log');
//            if (in_array($_name, $filterChildren)) {
//                Mage::log("adding '$_name'!", null, 'trs_guide.log');
//                $this->_filter->addLink($_block);
//            }
//        }
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
            Mage::log("_filter chain state: [".print_r($this->_filter->getChainState(), true).']', null, 'trs_guide.log');
            Mage::log("this chain state: [".print_r($this->getChainState(), true).']', null, 'trs_guide.log');
            return $this->_filter->getChainState();
        }
        return 'no chain state';
        //return $this->_filter->getChainState();
    }
}
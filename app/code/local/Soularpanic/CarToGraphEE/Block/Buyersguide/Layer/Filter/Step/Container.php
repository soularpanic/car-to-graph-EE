<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Step_Container
    extends Mage_Catalog_Block_Layer_Filter_Abstract {

    protected $_steps;


    public function _construct() {
        parent::_construct();
        Mage::log("Constructing step container", null, 'trs_guide.log');
        $this->_steps = [];
        $this->_filterModelName = 'cartographee/buyersguide_layer_filter_chain';
    }


    protected function _prepareFilter() {
        $this->_filter->addLinks($this->getSteps());
        return $this;
    }


    public function getSteps() {
        Mage::log("Getting steps....", null, 'trs_guide.log');
        if (!$this->_steps) {
            $steps = [];
            $children = $this->getChild();
            foreach ($children as $child) {
                $childName = $child->getNameInLayout();
                Mage::log("Processing [{$childName}]...", null, 'trs_guide.log');

                $matches = [];
                $matched = preg_match('/^step_(\d+)$/', $childName, $matches);
                if ($matched) {
                    $stepNumber = $matches[1];
                    Mage::log("Adding [{$childName}] to step list at position [{$stepNumber}]", null, 'trs_guide.log');
                    $steps[$stepNumber] = $child;
                }
            }
//
//            if (count($steps) === 0) {
//                $steps[] =
//            }

            $this->_steps = $steps;
        }
        return $this->_steps;
    }
}
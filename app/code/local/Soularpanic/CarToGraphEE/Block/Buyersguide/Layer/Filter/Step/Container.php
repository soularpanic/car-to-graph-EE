<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Step_Container
    extends Mage_Catalog_Block_Layer_Filter_Abstract {

    protected $_steps;


    public function _construct() {
        parent::_construct();
        Mage::helper('cartographee')->log("Constructing step container");
        $this->_steps = [];
        $this->_filterModelName = 'cartographee/buyersguide_layer_filter_chain';
    }


    protected function _prepareFilter() {
        $this->_filter->addLinks($this->getSteps());
        return $this;
    }


    public function getSteps() {
        $logger = Mage::helper('cartographee');
        $logger->log("Getting steps....");
        if (!$this->_steps) {
            $steps = [];
            $children = $this->getChild();
            foreach ($children as $child) {
                $childName = $child->getNameInLayout();
                $logger->log("Processing [{$childName}]...");

                $matches = [];
                $matched = preg_match('/^step_(\d+)$/', $childName, $matches);
                if ($matched) {
                    $stepNumber = $matches[1];
                    $logger->log("Adding [{$childName}] to step list at position [{$stepNumber}]");
                    $steps[$stepNumber] = $child;
                }
            }

            $this->_steps = $steps;
        }
        return $this->_steps;
    }
}
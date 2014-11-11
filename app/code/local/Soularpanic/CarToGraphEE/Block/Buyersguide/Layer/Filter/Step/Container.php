<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Step_Container
    extends Mage_Core_Block_Template {

    public function __construct() {
        Mage::log("Constructing step container", null, 'trs_guide.log');
        parent::__construct();
    }


    public function getSteps() {
        Mage::log("Getting steps....", null, 'trs_guide.log');
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
        return $steps;
    }
}
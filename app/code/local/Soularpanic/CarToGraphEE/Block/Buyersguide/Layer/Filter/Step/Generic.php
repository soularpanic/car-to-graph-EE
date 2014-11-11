<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Step_Generic
extends Mage_Core_Block_Template {

    protected $_stepConfig;

    public function __construct() {
        $_stepConfig = [];
        parent::__construct();
    }

    public function setStepConfig($config) {
        Mage::log("step config: ".print_r($config, true), null, 'trs_guide.log');
        $this->_stepConfig = $config;
    }

    public function getQuestion() {
        return $this->_stepConfig['step_question'];
    }

    public function getOptions() {
        return $this->_stepConfig['options'];
    }
}
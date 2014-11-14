<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Delegate
    extends Mage_Catalog_Model_Layer_Filter_Abstract {

    protected $_delegates = [];
    public function __construct() {
        parent::__construct();
        $this->_requestVar = 'car';
    }


    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        Mage::log("Applying delegate filter - start", null, 'trs_guide.log');
        foreach ($this->_delegates as $_delegate) {
            $_delegate->setLayer($this->getLayer())->init();
        }
        return $this;
    }


    public function addDelegate($filterBlock) {
        $this->_delegates[] = $filterBlock;
    }
}
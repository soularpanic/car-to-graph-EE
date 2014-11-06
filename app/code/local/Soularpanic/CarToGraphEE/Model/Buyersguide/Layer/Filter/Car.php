<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Car
    extends Mage_Catalog_Model_Layer_Filter_Abstract {


    public function __construct() {
        parent::__construct();
        $this->_requestVar = 'car';
    }


    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        Mage::log("Applying car filter - start", null, 'trs_guide.log');
        $carArr = $request->getParam($this->getRequestVar());
        Mage::log("carArr: [".print_r($carArr, true)."]", null, 'trs_guide.log');
        return $this;
    }
}
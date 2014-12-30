<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Noop
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        Mage::log("I am noop", null, 'trs_guide.log');
        return false;
    }

}
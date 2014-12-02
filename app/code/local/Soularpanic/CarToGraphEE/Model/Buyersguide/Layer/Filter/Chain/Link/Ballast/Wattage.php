<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Ballast_Wattage
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Configurable {

    protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        Mage::log("in the wattage chainapply method", null, 'trs_guide.log');
        return parent::_chainApply($request, $filterBlock);
    }

}
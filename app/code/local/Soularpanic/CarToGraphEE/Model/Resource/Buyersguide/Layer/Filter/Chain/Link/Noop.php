<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Noop
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option) {
//        $action = $option->getAction();
        Mage::log("nooping....", null, 'trs_guide.log');
        //$collection = $filter->getLayer()->getProductCollection();
//        $actionHelper = Mage::helper('cartographee/buyersguide_action');
//
//        $actionHelper->applyActionToCollection($filter, $action);

        return $this;
    }
}
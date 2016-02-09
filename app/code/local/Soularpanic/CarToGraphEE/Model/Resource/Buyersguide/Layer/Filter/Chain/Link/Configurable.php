<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Configurable
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option) {
        $action = $option->getAction();
        Mage::helper('cartographee')->log("applying configurable filter in resource; action={$action}");
        if (!$action) {
            return true;
        }

        $actionHelper = Mage::helper('cartographee/buyersguide_action');

        return $actionHelper->applyActionToCollection($filter, $action);
    }
}
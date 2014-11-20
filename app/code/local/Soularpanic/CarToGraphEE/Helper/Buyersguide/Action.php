<?php
class Soularpanic_CarToGraphEE_Helper_Buyersguide_Action
    extends Mage_Core_Helper_Abstract {


    public function isTerminal($actionStr) {
        return strpos($actionStr, 'sku:') === 0;
    }


    public function applyActionToCollection($filter, $actionStr, $resource) {
        Mage::log('applying action to collection - start', null, 'trs_guide.log');
        $collection = $filter->getLayer()->getProductCollection();
        list($action, $value) = explode(':', $actionStr, 2);
        Mage::log("action: [{$action}]; value: [{$value}]", null, 'trs_guide.log');
        if ($action == 'sku') {
            Mage::log("applying sku action", null, 'trs_guide.log');
            $select = $collection->getSelect()
                //->from($resource->getMainTable())
                ->where("e.sku = '{$value}'");
            //Mage::log("Collection sql: ".$collection->getSelect()->__toString(), null, 'trs_guide.log');
        }

    }

}
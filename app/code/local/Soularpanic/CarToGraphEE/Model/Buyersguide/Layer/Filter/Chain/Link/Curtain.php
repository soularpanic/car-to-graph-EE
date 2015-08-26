<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Curtain
extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract {
    protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        $car = $request->getParam('car');
        if ($car) {
            return true;
        }

        $collection = $filterBlock->getLayer()->getProductCollection();
        $select = $collection->getSelect();
        $select->limit(1); // zend won't let me limit to 0
        $collection->clear();
        $collection->load(); // reload the collection

        // remove the only item from the collection
        foreach ($collection->getItems() as $key => $item) {
            $collection->removeItemByKey($key);
        }

        return false;
    }
}
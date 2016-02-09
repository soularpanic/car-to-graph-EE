<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Hidsystems_Wattage
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option)
    {
        $logger = Mage::helper('cartographee');
        $logger->log("HID Systems Wattage Resource applying...");
        $logger->log("Option: ".print_r($option, true));
        $productId = $option->getProductId();

        $_dfBundleTarget = 'HID Ballats';
        $f = Mage::helper('cartographee/buyersguide_action')->toDirectFitTableAlias($_dfBundleTarget);


        $state = $filter->getChainState();

        $collection = $filter->getLayer()->getProductCollection();
        $directFitSelect = $collection->getSelect();

        $directFitSelect
            ->joinLeft([$f => new Zend_Db_Expr("(select $productId as ballast)")],
                "true",
                ["preselect_$f" => "$f.ballast"]);
        $logger->log("wattage sql: ".$directFitSelect->__toString());

        $state['action'] = 'step:2';
        $filter->setChainState($state);

        $logger->log("Wattage SQL:\n{$directFitSelect->__toString()}");
        $logger->log("Request vars:\n".print_r(Mage::app()->getRequest()->getParams(), true));
        return $this;
    }

}
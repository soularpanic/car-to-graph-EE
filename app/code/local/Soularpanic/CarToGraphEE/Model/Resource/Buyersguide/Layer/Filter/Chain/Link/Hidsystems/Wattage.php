<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Hidsystems_Wattage
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option)
    {
        Mage::log("HID Systems Wattage Resource applying...", null, 'trs_guide.log');
        Mage::log("Option: ".print_r($option, true), null, 'trs_guide.log');
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
        Mage::log("wattage sql: ".$directFitSelect->__toString(), null, 'trs_guide.log');

        $state['action'] = 'step:2';
        $filter->setChainState($state);

        Mage::log("Wattage SQL:\n{$directFitSelect->__toString()}", null, 'trs_guide.log');
        Mage::log("Request vars:\n".print_r(Mage::app()->getRequest()->getParams(), true), null, 'trs_guide.log');
        return $this;
    }

}
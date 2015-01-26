<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Hidsystems_Wattage
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option)
    {
        Mage::log("HID Systems Wattage Resource applying...", null, 'trs_guide.log');
        Mage::log("Option: ".print_r($option, true), null, 'trs_guide.log');
        $value = $option->getValue();
        if ($value === 'wattage_watt35') {
            $wattage = '35';
        }
        else if ($value === 'wattage_watt55') {
            $wattage = '55';
        }
        else {
            Mage::log("IDK what to do with this value: [$value]", null, 'trs_guide.log');
            return $this;
        }

        $_dfBundleTarget = 'HID Ballats';
        $f = Mage::helper('cartographee/buyersguide_action')->toDirectFitTableAlias($_dfBundleTarget);


        $state = $filter->getChainState();

        $collection = $filter->getLayer()->getProductCollection();
        $directFitSelect = $collection->getSelect();
        $fallback = "'XB-BALLAST-$wattage'";

        $directFitSelect
            ->joinLeft([$f => new Zend_Db_Expr("(select $fallback as ballast)")],
                "true",
                ["preselect_$f" => "$f.ballast"]);
        Mage::log("wattage sql: ".$directFitSelect->__toString(), null, 'trs_guide.log');

        $state['action'] = 'step:done';
        $filter->setChainState($state);

        Mage::log("Wattage SQL:\n{$directFitSelect->__toString()}", null, 'trs_guide.log');
        Mage::log("Request vars:\n".print_r(Mage::app()->getRequest()->getParams(), true), null, 'trs_guide.log');
        return $this;
    }

}
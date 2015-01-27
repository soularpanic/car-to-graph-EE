<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Completekits_Ballast
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    private $_PREV_STEP_VAR_NAME = 'step_2';

    public function applyFilterToCollection($filter, $option)
    {
        Mage::log("Kits Ballast Resource applying...", null, 'trs_guide.log');
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


        $collection = $filter->getLayer()->getProductCollection();
        $directFitSelect = $collection->getSelect();
        $ballastSku = "'XB-BALLAST-$wattage,AMP-IGNITER-R,D2S-IGNITER-R,IGNITER-AMP,IGNITER-D2S'";

        $directFitSelect
            ->joinLeft([$f => new Zend_Db_Expr("(select $ballastSku as ballast)")],
                "true",
                ["preselect_$f" => "$f.ballast"]);
        Mage::log("wattage sql: ".$directFitSelect->__toString(), null, 'trs_guide.log');


        Mage::log("Request vars:\n".print_r(Mage::app()->getRequest()->getParams(), true), null, 'trs_guide.log');
        return $this;
    }

}
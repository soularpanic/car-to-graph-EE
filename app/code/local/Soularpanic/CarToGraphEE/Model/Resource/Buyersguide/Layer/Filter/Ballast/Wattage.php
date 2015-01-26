<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Ballast_Wattage
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option) {
        Mage::log("in the wattage resource kerjigger! action=({$option})", null, 'trs_guide.log');
        return $this;
    }

}
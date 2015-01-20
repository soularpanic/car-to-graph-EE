<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Projectorsupplement
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option) {
        Mage::log("I'm projectorsupplement, bitch!", null, 'trs_guide.log');
        return $this;
    }
}
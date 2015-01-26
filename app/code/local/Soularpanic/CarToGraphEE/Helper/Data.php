<?php
class Soularpanic_CarToGraphEE_Helper_Data
    extends Mage_Core_Helper_Abstract {

    public function log($txt) {
        Mage::log($txt, null, 'trs_guide.log');
    }

}
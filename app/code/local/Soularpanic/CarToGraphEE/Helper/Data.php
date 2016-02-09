<?php
class Soularpanic_CarToGraphEE_Helper_Data
    extends Mage_Core_Helper_Abstract {

    public function log($txt) {
        $shouldLog = Mage::getStoreConfig('trs_logging/logs/cartographee_logging');
        if ($shouldLog) {
            Mage::log($txt, null, 'trs_guide.log');
        }
    }

}
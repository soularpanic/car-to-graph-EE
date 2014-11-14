<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Car
    extends Mage_Catalog_Model_Layer_Filter_Abstract {


    public function __construct() {
        parent::__construct();
        $this->_requestVar = 'car';
    }


    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        Mage::log("Applying car filter - start", null, 'trs_guide.log');
        $carId = $request->getParam($this->getRequestVar());
        Mage::log("carArr: [".print_r($carId, true)."]", null, 'trs_guide.log');
        if ($carId) {
            $this->_getResource()->applyFilterToCollection($this, $carId);
        }
        return $this;
    }

    protected function _getResource() {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('cartographee/buyersguide_layer_filter_car');
        }
        return $this->_resource;
    }
}
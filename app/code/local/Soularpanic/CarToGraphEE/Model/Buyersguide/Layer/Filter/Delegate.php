<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Delegate
    extends Mage_Catalog_Model_Layer_Filter_Abstract {

    protected $_delegates = [];
    public function _construct() {
        parent::_construct();
        $this->_requestVar = 'buyersGuideActive';
    }


    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        Mage::log("Applying delegate filter - start", null, 'trs_guide.log');
        $shouldApply = $request->getParam($this->getRequestVar());
        if ($shouldApply) {
            foreach ($this->_delegates as $_delegate) {
                $_delegate->setLayer($this->getLayer())->init();
            }
        }
        return $this;
    }


    public function addDelegate($filterBlock) {
        $this->_delegates[] = $filterBlock;
    }
}
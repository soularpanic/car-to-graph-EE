<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain
    extends Mage_Catalog_Model_Layer_Filter_Abstract {

    protected $_links = [];

    public function __construct() {
        parent::__construct();
        $this->_requestVar = 'car';
    }

    public function addLink($linkBlock) {
        $this->_links[] = $linkBlock->getChainLink();
    }


    public function addLinks($linkBlockArr) {
        foreach ($linkBlockArr as $linkBlock) {
            $this->addLink($linkBlock);
        }
    }


    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {

        Mage::log("Applying delegate filter - start", null, 'trs_guide.log');
        $chain = $this->_links;
        $car = reset($chain);
        $cdr = array_slice($chain, 1, null, true);
        $result = $car->evaluate('', $cdr, $request, $filterBlock);
        Mage::log("result: [{$result}]", null, 'trs_guide.log');
        return $this;
    }
}
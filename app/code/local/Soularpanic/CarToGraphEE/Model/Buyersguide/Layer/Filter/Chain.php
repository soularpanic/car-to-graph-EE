<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract {
    //extends Mage_Catalog_Model_Layer_Filter_Abstract {


    protected $_links = [];

    public function _construct() {
        parent::_construct();
        $this->_requestVar = 'car';
    }

    public function addLink($linkBlock) {
        //$this->_links[] = $linkBlock->getChainLink();
        $this->_links[] = $linkBlock;
    }


    public function addLinks($linkBlockArr) {
        foreach ($linkBlockArr as $linkBlock) {
            $this->addLink($linkBlock);
        }
    }


    protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        if (!$this->getChainState()) {
            $blockChainState = $filterBlock->getChainState();
            $this->setChainState($blockChainState ?: []);
        }
        return true;
    }


    protected function _getChain(Zend_Controller_Request_Abstract $request, $filterBlock) {
        return $this->_links;
    }


//    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
//
//        Mage::log("Applying chain filter - start", null, 'trs_guide.log');
//        $chain = $this->_links;
//        $car = reset($chain);
//        $cdr = array_slice($chain, 1, null, true);
//        Mage::log("cdr:".count($cdr), null, 'trs_guide.log');
//        Mage::log('setting layer...', null, 'trs_guide.log');
//        $car->setLayer($this->getLayer());
//        Mage::log('setting chain...', null, 'trs_guide.log');
//        $car->setChain($cdr);
//        Mage::log('setting chain state...', null, 'trs_guide.log');
//        $car->setChainState([]);
//        Mage::log('calling init...', null, 'trs_guide.log');
//        $car->init();
//        //$result = $car->evaluate('', $cdr, $request, $filterBlock);
//        //Mage::log("result: [{$result}]", null, 'trs_guide.log');
//        return $this;
//    }
}
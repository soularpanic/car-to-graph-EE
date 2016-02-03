<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    protected $_links = [];

    public function _construct() {
        parent::_construct();
        $this->_requestVar = 'car';
    }

    public function addLink($linkBlock) {
        $logger = Mage::helper('cartographee');
        $logger->log("adding block ({$linkBlock->getNameInLayout()}) to chain");
        $this->_links[] = $linkBlock;
        $logger->log("chain length is now ".count($this->_links));
    }


    public function addLinks($linkBlockArr) {
        foreach ($linkBlockArr as $linkBlock) {
            $this->addLink($linkBlock);
        }
    }


    protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        $logger = Mage::helper('cartographee');
        $logger->log("_chainApply called on Chain block");
        if (!$this->getChainState()) {
            $blockChainState = $filterBlock->getChainState();
            $this->setChainState($blockChainState ?: []);
        }
        return true;
    }


    protected function _getChain(Zend_Controller_Request_Abstract $request, $filterBlock) {
        return $this->_links;
    }
}
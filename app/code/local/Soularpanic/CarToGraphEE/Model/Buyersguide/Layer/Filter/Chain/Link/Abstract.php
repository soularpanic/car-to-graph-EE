<?php
abstract class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract
    extends Mage_Catalog_Model_Layer_Filter_Abstract {

    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        $logger = Mage::helper('cartographee/data');
        $logger->log("at abstract apply start, chain state: [".print_r($this->getChainState(), true).']');
        $continueChain = $this->_chainApply($request, $filterBlock);
        $logger->log("after _chainApply, chain state: [".print_r($this->getChainState(), true).']', null, 'trs_guide.log');

        $chain = $this->_getChain($request, $filterBlock);
        $logger->log("The chain has ".count($chain)." elements remaining");

        if (count($chain) && $continueChain) {
            $car = reset($chain);
            $logger->log("next chain link is ".get_class($car));
            $cdr = array_slice($chain, 1, null, true);
            $logger->log("after that, we have ".count($cdr)." more");
            $car->setLayer($this->getLayer());
            $car->setChain($cdr);
            $car->setChainState($this->getChainState());
            $car->init();
            $this->setChainState($car->getChainState());
        }

        $filterBlock->setChainState($this->getChainState());

        return $this;
    }

    protected function _getChain(Zend_Controller_Request_Abstract $request, $filterBlock) {
        return $filterBlock->getChain();
    }

    abstract protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock);

}
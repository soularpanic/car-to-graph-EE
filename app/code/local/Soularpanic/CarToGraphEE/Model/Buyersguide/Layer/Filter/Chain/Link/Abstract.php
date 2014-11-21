<?php
abstract class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract
    extends Mage_Catalog_Model_Layer_Filter_Abstract {

    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        //Mage::log("at abstract apply start, chain state: [".print_r($this->getChainState(), true).']', null, 'trs_guide.log');
        $continueChain = $this->_chainApply($request, $filterBlock);
        //Mage::log("after _chainApply, chain state: [".print_r($this->getChainState(), true).']', null, 'trs_guide.log');

        $chain = $this->_getChain($request, $filterBlock);
        if (count($chain) && $continueChain) {
            $car = reset($chain);
            //Mage::log("next chain link is ".get_class($car), null, 'trs_guide.log');
            $cdr = array_slice($chain, 1, null, true);
            //Mage::log("after that, we have ".count($cdr)." more", null, 'trs_guide.log');
            $car->setLayer($this->getLayer());
            $car->setChain($cdr);
            $car->setChainState($this->getChainState());
            $car->init();
            //Mage::log("after it was all over, chain state was [".print_r($car->getChainState(), true).']', null, 'trs_guide.log');
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
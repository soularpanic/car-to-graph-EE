<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Car
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract {
    //extends Mage_Catalog_Model_Layer_Filter_Abstract {


    public function __construct() {
        parent::_construct();
        $this->_requestVar = 'car';
    }


    protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        Mage::log("Applying car filter - start", null, 'trs_guide.log');
        $noFitAction = $filterBlock->getNoFitAction();
        Mage::log("no fit action: {$noFitAction}", null, 'trs_guide.log');
        $carId = $request->getParam($this->getRequestVar());
//        Mage::log("carArr: [".print_r($carId, true)."]", null, 'trs_guide.log');
//        Mage::log('filter block class: '.get_class($filterBlock), null, 'trs_guide.log');
//        $chainState = $filterBlock->getChainState();
//        Mage::log('chain state: ['.print_r($chainState, true).']', null, 'trs_guide.log');
//        Mage::log('chain? '.get_class($filterBlock->getChain()).'/'.count($filterBlock->getChain()), null, 'trs_guide.log');
        if ($carId) {
            Mage::log("getting resource", null, 'trs_guide.log');
            $resource = $this->_getResource();
            if ($noFitAction) {
                Mage::log("setting no fit action to [{$noFitAction}]", null, 'trs_guide.log');
                $resource->setNoFitAction($noFitAction);
            }
            Mage::log("applying!", null, 'trs_guide.log');
            $resource->applyFilterToCollection($this, $carId);
        }
        $chainState = $this->getChainState();
        Mage::log("after resource application, chain state: [".print_r($chainState, true)."]", null, 'trs_guide.log');
        //return (!$filterBlock->getContinueOnDirectFit() && $chainState['has_direct_fit'] < 1);
        return true;

//
//        $chain = $filterBlock->getChain();
//        if (count($chain)) {
//            $car = reset($chain);
//            Mage::log("next chain link is ".get_class($car), null, 'trs_guide.log');
//            $cdr = array_slice($chain, 1, null, true);
//            Mage::log("after that, we have ".count($cdr)." more", null, 'trs_guide.log');
//            $car->setLayer($this->getLayer());
//            $car->setChain($cdr);
//            $car->setChainState($this->getChainState());
//            //$car->setChainState([]);
//            $car->init();
//        }
//
//        return $this;
    }

    protected function _getResource() {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('cartographee/buyersguide_layer_filter_car');
        }
        return $this->_resource;
    }
}
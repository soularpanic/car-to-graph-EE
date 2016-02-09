<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Car
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract {
 

    public function __construct() {
        parent::_construct();
        $this->_requestVar = 'car';
    }


    protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        $logger = Mage::helper('cartographee');
        $logger->log("Applying car filter - start");
        $noFitAction = $filterBlock->getNoFitAction();
        $logger->log("no fit action: {$noFitAction}");
        $directFitAction = $filterBlock->getDirectFitAction();
        $logger->log("direct fit action: {$directFitAction}");
        $bundleTargets = $filterBlock->getDirectFitBundleTargets();
        $logger->log("direct fit bundle targets: {$bundleTargets}");
        $carId = $request->getParam($this->getRequestVar());
        // Magento concatenates the get param for some reason
        $carGetParamPosition = strpos($carId, '?car=');
        if ($carGetParamPosition !== false) {
            $carId = substr($carId, 0, $carGetParamPosition);
        }

        if ($carId) {
            $logger->log("getting resource");
            $resource = $this->_getResource();
            if ($noFitAction) {
                $logger->log("setting no fit action to [{$noFitAction}]");
                $resource->setNoFitAction($noFitAction);
            }
            if ($directFitAction) {
                $resource->setDirectFitAction($directFitAction);
            }
            if ($bundleTargets) {
                $resource->setDirectFitBundleTargets(array_map('trim', explode(',', $bundleTargets)));
            }

            $logger->log("applying!");
            $resource->applyFilterToCollection($this, $carId);
        }
        $chainState = $this->getChainState();
        $logger->log("after resource application, chain state: [".print_r($chainState, true)."]");
        return true;
    }

    protected function _getResource() {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('cartographee/buyersguide_layer_filter_car');
        }
        return $this->_resource;
    }
}
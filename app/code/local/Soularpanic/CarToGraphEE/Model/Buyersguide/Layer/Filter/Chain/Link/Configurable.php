<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Configurable
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract {
    //extends Mage_Catalog_Model_Layer_Filter_Abstract {

    protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        Mage::log("chain link configurable starting", null, 'trs_guide.log');
        $requestVar = $this->getId();
        Mage::log("configurable searching for '{$requestVar}'", null, 'trs_guide.log');
        $value = $request->getParam($requestVar);
        $chainState = $filterBlock->getChainState();
        Mage::log("value: {$value}", null, 'trs_guide.log');
        Mage::log("chainState: ".print_r($chainState, true), null, 'trs_guide.log');


        if ($value) {
            foreach ($this->getOptions() as $option) {
                if ($option->getId() === $value) {
                    $selectedAction = $option->getAction();
                }
            }
        }

        $chainState['action'] = $selectedAction;
        Mage::log("Action: {$selectedAction}", null, 'trs_guide.log');
        // do something to the collection
        if ($selectedAction) {
            $this->_getResource()->applyFilterToCollection($this, $selectedAction);
        }
        $actionHelper = Mage::helper('cartographee/buyersguide_action');
        return !$actionHelper->isTerminal($selectedAction);
    }

    protected function _getResource() {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('cartographee/buyersguide_layer_filter_chain_link_configurable');
        }
        return $this->_resource;
    }
}
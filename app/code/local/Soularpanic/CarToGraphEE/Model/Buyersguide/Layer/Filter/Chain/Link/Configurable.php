<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Configurable
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract {
    //extends Mage_Catalog_Model_Layer_Filter_Abstract {

    protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        Mage::log("chain link configurable starting", null, 'trs_guide.log');
        Mage::log("apply to direct fit? [{$this->getApplyToDirectFit()}]", null, 'trs_guide.log');


        $chainState = $filterBlock->getChainState();
        if (!$this->getApplyToDirectFit() && $chainState['has_direct_fit'] > 0) {
            Mage::log("this link does not apply once direct fits are found", null, 'trs_guide.log');
            return false;
        }

        $requestVar = $this->getId();
        Mage::log("configurable searching for '{$requestVar}'", null, 'trs_guide.log');
        $value = $request->getParam($requestVar);
        Mage::log("value: {$value}", null, 'trs_guide.log');
        Mage::log("(filterblock) chainState: [".print_r($chainState, true).']', null, 'trs_guide.log');
        Mage::log("(this) chainState: [".print_r($this->getChainState(), true).']', null, 'trs_guide.log');

        if ($value) {
            foreach ($this->getOptions() as $option) {
                if ($option->getId() === $value) {
                    $selectedAction = $option->getAction();
                    $selectedAction = str_replace('~', $option->getId(), $selectedAction);
                }
            }
        }

        if ($selectedAction) {
            $chainState['action'] = $selectedAction;
            $this->setChainState($chainState);
            Mage::log("chain state at action check: [".print_r($this->getChainState(), true).']', null, 'trs_guide.log');
            // do something to the collection

            $this->_getResource()->applyFilterToCollection($this, $selectedAction);

            $actionHelper = Mage::helper('cartographee/buyersguide_action');
            return !$actionHelper->isTerminal($selectedAction);
        }

        return false;
    }

    protected function _getResource() {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('cartographee/buyersguide_layer_filter_chain_link_configurable');
        }
        return $this->_resource;
    }
}
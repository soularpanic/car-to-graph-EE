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

        if ($this->getApplyToDirectFit() && !$chainState['has_direct_fit']) {
            Mage::log("this link applies to direct fits but there aren't any!", null, 'trs_guide.log');
            return false;
        }

        $requestVar = $this->getId();
        Mage::log("configurable searching for '{$requestVar}'", null, 'trs_guide.log');
        $value = $request->getParam($requestVar);
        Mage::log("value: {$value}", null, 'trs_guide.log');
        Mage::log("(filterblock) chainState: [".print_r($chainState, true).']', null, 'trs_guide.log');
        Mage::log("(this) chainState: [".print_r($this->getChainState(), true).']', null, 'trs_guide.log');

        $prevGroup = null;
        if ($this->getChainState()) {
            $action = $this->getChainState()['action'];
            $parts = explode('/', $action);
            if (count($parts) > 1) {
                $prevGroup = $parts[1];
            }
        }
        Mage::log("previous group was <$prevGroup>", null, 'trs_guide.log');


        if ($value) {
            foreach ($this->getOptions() as $option) {
//                Mage::log("checking this option:\n".print_r($option, true), null, 'trs_guide.log');
                if ($option->getValue() === $value) {
                    if ($prevGroup) {
                        if ($prevGroup === $option->getGroupId()) {
                            $selectedOption = $option;
                            break;
                        }
                    }
                    else {
                        $selectedOption = $option;
                        break;
                    }
                    //$selectedAction = str_replace('~', $option->getId(), $selectedAction);
                }
            }
            if ($value === '_SKIP' && !isset($selectedOption)) {
                $selectedOption = new Varien_Object([
                    'id' => -1,
                    'action' => '_SKIP',
                    'value' => '_SKIP'
                ]);
            }
        }

        Mage::log("selected option: ".($selectedOption ? "{$selectedOption->getId()}/{$selectedOption->getAction()}" : "NOTHING"), null, 'trs_guide.log');

        if ($selectedOption) {
            if ($selectedOption->getAction()) {
                $chainState['action'] = $selectedOption->getAction();
            }
            $this->setChainState($chainState);
            Mage::log("chain state at action check: [".print_r($this->getChainState(), true).']', null, 'trs_guide.log');
            // do something to the collection
            Mage::log("resource model: -{$filterBlock->getModel()}-", null, 'trs_guide.log');
            $filterResponse = $this->_getResource($filterBlock->getModel())->applyFilterToCollection($this, $selectedOption);
            return is_bool($filterResponse) ? $filterResponse : !(Mage::helper('cartographee/buyersguide_action')->isTerminal($selectedOption));
        }

        return false;
    }

    protected function _getResource($resourceModelName) {
        $_modelName = $resourceModelName ?: 'cartographee/buyersguide_layer_filter_chain_link_configurable';
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel($_modelName);
        }
        return $this->_resource;
    }
}
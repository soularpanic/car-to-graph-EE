<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Configurable
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    protected function _chainApply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        $logger = Mage::helper('cartographee');
        $logger->log("chain link configurable starting");
        $logger->log("apply to direct fit? [{$this->getApplyToDirectFit()}]");


        $chainState = $filterBlock->getChainState();
        if (!$this->getApplyToDirectFit() && $chainState['has_direct_fit'] > 0) {
            $logger->log("this link does not apply once direct fits are found");
            return false;
        }

        if ($this->getApplyToDirectFit() && !$chainState['has_direct_fit']) {
            $logger->log("this link applies to direct fits but there aren't any!");
            return false;
        }

        $requestVar = $this->getId();
        $logger->log("configurable searching for '{$requestVar}'");
        $value = $request->getParam($requestVar);
        $logger->log("value: {$value}");
        $logger->log("(filterblock) chainState: [".print_r($chainState, true).']');
        $logger->log("(this) chainState: [".print_r($this->getChainState(), true).']');

        $prevGroup = null;
        if ($this->getChainState()) {
            $action = $this->getChainState()['action'];
            $parts = explode('/', $action);
            if (count($parts) > 1) {
                $prevGroup = $parts[1];
                $commandEndPos = strpos($prevGroup, ';');
                if ($commandEndPos !== false) {
                    $prevGroup = substr($prevGroup, 0, $commandEndPos);
                }
            }
        }
        $logger->log("previous group was <$prevGroup>");


        if ($value) {
            foreach ($this->getOptions() as $option) {
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

        $logger->log("selected option: ".($selectedOption ? "{$selectedOption->getId()}/{$selectedOption->getAction()}" : "NOTHING"));

        if ($selectedOption) {
            if ($selectedOption->getAction()) {
                $chainState['action'] = $selectedOption->getAction();
            }
            $this->setChainState($chainState);
            $logger->log("chain state at action check: [".print_r($this->getChainState(), true).']');
            // do something to the collection
            $logger->log("resource model: -{$filterBlock->getModel()}-");
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
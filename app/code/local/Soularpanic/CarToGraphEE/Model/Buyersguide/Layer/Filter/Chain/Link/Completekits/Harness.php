<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Completekits_Harness
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Configurable {

    protected function _getResource($resourceModelName) {
        $_modelName = 'cartographee/buyersguide_layer_filter_chain_link_completekits_harness';
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel($_modelName);
        }
        return $this->_resource;
    }
}
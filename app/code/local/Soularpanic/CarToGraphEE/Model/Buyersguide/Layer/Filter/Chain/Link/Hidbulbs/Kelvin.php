<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Hidbulbs_Kelvin
    extends Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link_Configurable {

    protected function _getResource($resourceModelName) {
        $_modelName = 'cartographee/buyersguide_layer_filter_chain_link_hidbulbs_kelvin';
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel($_modelName);
        }
        return $this->_resource;
    }
}
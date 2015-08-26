<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_Filter_Curtain
    extends Mage_Catalog_Block_Layer_Filter_Abstract {


    public function _construct() {
        parent::_construct();
        $this->_filterModelName = 'cartographee/buyersguide_layer_filter_chain_link_curtain';
    }

}
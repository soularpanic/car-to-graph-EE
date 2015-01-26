<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Step_Image
    extends Varien_Object {


    public function render() {
        $rawUrl = $this->getUrl();
        if (!$rawUrl) {
            return '';
        }

        $url = Mage::getDesign()->getSkinUrl($rawUrl);
        $alt = $this->getAlt() ? " alt='{$this->getAlt()}'" : '';
        return "<img src='{$url}'{$alt} border='0'/>";
    }
}
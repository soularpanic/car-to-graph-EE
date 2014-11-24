<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Step_Option
    extends Varien_Object {

    public function getDisplayValue() {
        return ($this->getData['display_value'] ?: $this->getTitle());
    }

}
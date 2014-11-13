<?php
class Soularpanic_CarToGraphEE_Helper_Buyersguide_Config
    extends Mage_Core_Helper_Abstract {

    public function processStepConfigArray($stepConfigArr) {
        $data = [];

        $data['question'] = $stepConfigArr['step_question'];

        if (array_key_exists('step_image', $stepConfigArr)) {
            $imageData = $stepConfigArr['step_image'];
            $image = Mage::getModel('cartographee/buyersguide_layer_filter_step_image');
            $image->setData($imageData);
            $data['image'] = $image;
        }

        $options = [];
        foreach ($stepConfigArr['options'] as $id => $values) {
            $option = Mage::getModel('cartographee/buyersguide_layer_filter_step_option');
            $option->setData($values)->setId($id);
            $options[] = $option;
        }
        $data['options'] = $options;

        return $data;
    }

}
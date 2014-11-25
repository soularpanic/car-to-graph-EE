<?php
class Soularpanic_CarToGraphEE_Helper_Buyersguide_Config
    extends Soularpanic_CarToGraphEE_Helper_Data {

    public function processStepConfigArray($stepConfigArr) {
        $data = [];

        if (array_key_exists('id', $stepConfigArr)) {
            $data['id'] = $stepConfigArr['id'];
        }

        if (array_key_exists('display_name', $stepConfigArr)) {
            $data['display_name'] = $stepConfigArr['display_name'];
        }

        $data['apply_to_direct_fit'] = $stepConfigArr['apply_to_direct_fit'];

        $data['question'] = $stepConfigArr['step_question'];
        $data['aspects'] = $stepConfigArr['performance_aspects'];

        if (array_key_exists('step_image', $stepConfigArr)) {
            $data['image'] = $this->_buildImage($stepConfigArr['step_image']);
        }

        $options = [];
        foreach ($stepConfigArr['options'] as $id => $values) {

            if (array_key_exists('binary', $values)) {
                $binary = $stepConfigArr['binary'];
                $this->log("binary: ".print_r($binary, true));
                foreach ($values['binary'] as $binId => $binValues) {
                    $this->log("binid: {$binId}; binValues: ".print_r($binValues, true));
                    $combined = array_merge($binary[$binId], $binValues);
                    if (array_key_exists('image', $combined)) {
                        $combined['image'] = $this->_buildImage($combined['image']);
                    }
                    $this->log("combined: ".print_r($combined, true));
                    $options[] = $this->_buildOption($id, $combined);
                }
            }
            else {
                $options[] = $this->_buildOption($id, $values);
            }
        }
        $data['options'] = $options;

        return $data;
    }

    protected function _buildOption($id, $data) {
        $data['active'] = array_key_exists('active', $data)
            ? filter_var($data['active'], FILTER_VALIDATE_BOOLEAN)
            : true;
        $option = Mage::getModel('cartographee/buyersguide_layer_filter_step_option');
        $option->setData($data)->setId($id);
        return $option;
    }


    protected function _buildImage($data) {
        $image = Mage::getModel('cartographee/buyersguide_layer_filter_step_image');
        $image->setData($data);
        return $image;
    }

}
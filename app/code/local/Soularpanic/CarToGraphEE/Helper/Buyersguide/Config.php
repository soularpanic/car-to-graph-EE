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

        $data['step_desc'] = $stepConfigArr['step_desc'];
        $data['apply_to_direct_fit'] = $stepConfigArr['apply_to_direct_fit'];
        $data['question_style_override'] = $stepConfigArr['question_style_override'];
        $data['question'] = $stepConfigArr['step_question'];
        $data['aspects'] = $stepConfigArr['performance_aspects'];

        if (array_key_exists('step_image', $stepConfigArr)) {
            $data['image'] = $this->_buildImage($stepConfigArr['step_image']);
        }

        $options = [];
        foreach ($stepConfigArr['options'] as $id => $values) {

            if (array_key_exists('binary', $values)) {
                $binary = $stepConfigArr['binary'];
                foreach ($values['binary'] as $binId => $binValues) {
                    $combined = array_merge($binary[$binId], $binValues);
                    $combined['value'] = "{$id}_{$binId}";
                    $options[] = $this->_buildOption($id, $combined);
                }
            }
            else {
                foreach ($this->_buildOptions($id, $values) as $_option) {
                    $options[] = $_option;
                }
            }
        }
        $data['options'] = $options;
        return $data;
    }

    protected function _buildOptions($id, $data) {

        $options = [];
        if (is_array($data['action'])) {
            foreach ($data['action'] as $groupId => $groupAction) {
                $newData = $data;
                $newData['action'] = $groupAction;
                $newData['group_id'] = $groupId;
                $options[] = $this->_buildOption($id, $newData);
            }
        }
        else {
            $options[] = $this->_buildOption($id, $data);
        }
        return $options;
    }


    protected function _buildOption($id, $data) {
        $data['active'] = array_key_exists('active', $data)
            ? filter_var($data['active'], FILTER_VALIDATE_BOOLEAN)
            : true;
        if (array_key_exists('image', $data)) {
            $data['image'] = $this->_buildImage($data['image']);
        }
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
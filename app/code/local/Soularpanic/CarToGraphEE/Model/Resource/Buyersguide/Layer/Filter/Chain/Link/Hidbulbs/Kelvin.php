<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Hidbulbs_Kelvin
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option)
    {
        Mage::log('HID Bulbs Kelvin resource starting up...', null, 'trs_guide.log');
        $value = $option->getValue();

        $_dfBundleTarget = 'HID Bulbs';
        $dfBundleTarget = strtolower(str_replace(' ', '_', $_dfBundleTarget));
        $carAlias = "car_$dfBundleTarget";
        $linkAlias = "carlink_$dfBundleTarget";
        $optionProductAlias = "option_product_$dfBundleTarget";
        $fitOptionAlias = "fit_option_$dfBundleTarget";
        $attributeSetAlias = "attribute_set_$dfBundleTarget";
        $f = "f_$dfBundleTarget";


        //$chain = $filter->getChain();
        $state = $filter->getChainState();
//        $carId = $state['car_id'];

        $bulbAlias = 'f_hid_bulbs';
//        $nextStepValue = Mage::app()->getRequest()->getParam($this->_NEXT_STEP_VAR_NAME);
//        Mage::log("Next step value: ($nextStepValue)", null, 'trs_guide.log');
//        if ($fitSkuArr) {
        if ($value) {
            Mage::log("Altering SQL...", null, 'trs_guide.log');
            $collection = $filter->getLayer()->getProductCollection();
            $directFitSelect = $collection->getSelect();

            //$originalSelect = clone $directFitSelect;
//            $fitSkus = "('".implode("','", $fitSkuArr)."')";
            $likeStr = str_replace('0', '_', str_replace('K', '%', $value)).'K%';
            $columnAlias = "preselect_$dfBundleTarget";
            $directFitSelect
                ->join(['bulb_package_options' => $this->getTable('bundle/selection')],
                    "bulb_package_options.parent_product_id = e.entity_id",
                    [])
                ->joinLeft([$f => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
                    "$f.entity_id = bulb_package_options.product_id and $f.name like '$likeStr'",
                    [$columnAlias => "GROUP_CONCAT(DISTINCT $f.sku SEPARATOR ',')"])
//                ->orWhere("$f.sku is not null")
                ->where("$f.sku is not null")
                ->having("$columnAlias is not null");

            Mage::log("Shroud SQL:\n{$directFitSelect->__toString()}", null, 'trs_guide.log');

            $state['action'] = 'step:done';
            $filter->setChainState($state);
        }

//        $state['action'] = $refineFurther ? $option->getAction() : 'step:done';
//        $filter->setChainState($state);


        Mage::log("Request vars:\n".print_r(Mage::app()->getRequest()->getParams(), true), null, 'trs_guide.log');
        return $this;
    }

}

<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Completekits_Projector_Size
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    private $_NEXT_STEP_VAR_NAME = 'step_2';

    public function applyFilterToCollection($filter, $option)
    {
        Mage::log("Kits Projector Size Resource applying...", null, 'trs_guide.log');
        Mage::log("Option: ".print_r($option, true), null, 'trs_guide.log');
        $value = $option->getValue();

        $req = Mage::app()->getRequest();
        if ($value === 'tiny') {
            $fitSkuArr = ['MATCHBOX-S'];
            $req->setParam('step_5', 'wattage_watt35');

        }
        elseif ($value === 'small') {
            $fitSkuArr = ['MATCHBOX-S', 'MH16-LH', 'MH16-RH'];
            $req->setParam('step_5', 'wattage_watt35');
        }
        elseif ($value === 'medium') {
            $fitSkuArr = ['MH16-LH', 'MH16-RH', 'MD2S-3LH', 'MD2S-3RH'];
        }
        elseif ($value === 'large') {
            $fitSkuArr = ['MD2S-3LH', 'MD2S-3RH', 'FXR3-2.5LHD', 'FXR3-2.5RHD', 'FXR3-3LHD', 'FXR3-3RHD'];
        }
        else {
            Mage::log("IDK what to do with this value: [$value]", null, 'trs_guide.log');
            return $this;
        }

        $_dfBundleTarget = 'HID Projectors';
        $dfBundleTarget = strtolower(str_replace(' ', '_', $_dfBundleTarget));
        $carAlias = "car_$dfBundleTarget";
        $linkAlias = "carlink_$dfBundleTarget";
        $optionProductAlias = "option_product_$dfBundleTarget";
        $fitOptionAlias = "fit_option_$dfBundleTarget";
        $attributeSetAlias = "attribute_set_$dfBundleTarget";
        $f = "f_$dfBundleTarget";



        //$chain = $filter->getChain();
        $state = $filter->getChainState();
        $carId = $state['car_id'];

        $bulbAlias = 'f_hid_bulbs';
        $nextStepValue = Mage::app()->getRequest()->getParam($this->_NEXT_STEP_VAR_NAME);
        Mage::log("Next step value: ($nextStepValue)", null, 'trs_guide.log');
        if (!$nextStepValue) {
            Mage::log("Altering SQL...", null, 'trs_guide.log');
            $collection = $filter->getLayer()->getProductCollection();
            $directFitSelect = $collection->getSelect();

            //$originalSelect = clone $directFitSelect;
            $fitSkus = "('".implode("','", $fitSkuArr)."')";
            $columnAlias = "preselect_$dfBundleTarget";
            $directFitSelect
                ->joinLeft([$f => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
                    "$f.entity_id = package_options.product_id and $f.sku in $fitSkus",
                    [$columnAlias => "GROUP_CONCAT(DISTINCT $f.sku SEPARATOR ',')"])
                ->orWhere("$f.sku is not null")
                ->having("$columnAlias is not null");

            Mage::log("Projector Size SQL:\n{$directFitSelect->__toString()}", null, 'trs_guide.log');
        }

//        $state['action'] = $refineFurther ? $option->getAction() : 'step:done';
//        $filter->setChainState($state);


        Mage::log("Request vars:\n".print_r(Mage::app()->getRequest()->getParams(), true), null, 'trs_guide.log');
        return $this;
    }

}
<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Completekits_Projector_Performance
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    private $_NEXT_STEP_VAR_NAME = 'step_3';

    public function applyFilterToCollection($filter, $option)
    {
        Mage::log("Kits Projector Performance Resource applying...", null, 'trs_guide.log');
        Mage::log("Option: ".print_r($option, true), null, 'trs_guide.log');
        $value = $option->getValue();
        if ($value === 'MATCHBOX-R') {
                $fitSkuArr = ['MATCHBOX-S'];
        }
        elseif ($value === 'MH16.0-R') {
            $fitSkuArr = ['MH16-LH', 'MH16-RH'];
        }
        elseif ($value === 'MD2S3.0-R') {
            $fitSkuArr = ['MD2S-3LH', 'MD2S-3RH'];
        }
        elseif ($value === 'FXR3.0-R') {
            $fitSkuArr = ['FXR3-2.5LHD', 'FXR3-2.5RHD', 'FXR3-3LHD', 'FXR3-3RHD'];
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
        Mage::log("next step value is ($nextStepValue)", null, 'trs_guide.log');
        if (!$nextStepValue) {
            $collection = $filter->getLayer()->getProductCollection();
            $directFitSelect = $collection->getSelect();

            //$originalSelect = clone $directFitSelect;
//            $fitSkus = "('".implode("','", $fitSkuArr)."')";
//            $directFitSelect
//                ->joinLeft([$f => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
//                    "$f.entity_id = package_options.product_id and $f.sku in $fitSkus",
//                    ["preselect_$dfBundleTarget" => "GROUP_CONCAT(DISTINCT $f.sku SEPARATOR ',')"])
//                ->orWhere("$f.sku is not null");

            $fitSkus = "('".implode("','", $fitSkuArr)."')";
            $columnAlias = "preselect_$dfBundleTarget";
            $directFitSelect
                ->joinLeft([$f => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
                    "$f.entity_id = package_options.product_id and $f.sku in $fitSkus",
                    [$columnAlias => "GROUP_CONCAT(DISTINCT $f.sku SEPARATOR ',')"])
                ->orWhere("$f.sku is not null")
                ->having("$columnAlias is not null");

            Mage::log("Projector Performance SQL:\n{$directFitSelect->__toString()}", null, 'trs_guide.log');

            if ($value === 'MATCHBOX-R') {
                Mage::app()->getRequest()->setParam('step_3', '_SKIP');
                Mage::app()->getRequest()->setParam('step_4', '_SKIP');
            }
        }

//        $sqlString = "(select
//                    f.entity_id
//                    ,f.sku
//                    ,links.option, links.type
//                    ,cars.alt_id
//                from
//                    catalog_product_flat_1 as f
//                    inner join eav_attribute_set as eas
//                        on eas.attribute_set_id = f.attribute_set_id
//                    inner join cartographee_car_product_links as links
//                        on links.product_id = f.entity_id and eas.attribute_set_name = '$_dfBundleTarget'
//                    inner join cartographee_cars as cars
//                        on cars.entity_id = links.car_id and cars.alt_id = '$carId')";


//        $step2Value = Mage::app()->getRequest()->getParam('step_2');
//        $fallback = "null";
//        if ($step2Value) {
//            $fallback = $fitSkus . (in_array($step2Value, ['d2s', 'd2r']) ? "D2S" : "AMP") . "-DSP";
//        }
//
//        $directFitSelect
//            ->joinLeft([$f => new Zend_Db_Expr($sqlString)],
//                "$f.entity_id = package_options.product_id",
//                ["preselect_$dfBundleTarget" => "GROUP_CONCAT(DISTINCT IFNULL($f.sku, IF($bulbAlias.sku is not null, IF($bulbAlias.sku REGEXP 'D2[SR]', '{$fitSkus}D2S-DSP', '{$fitSkus}AMP-DSP') , '$fallback')) SEPARATOR ',')"])
//            ->orWhere("$f.sku is not null");
//
//        $refineFurther = false;
//        if (!$step2Value) {
//            foreach ($collection as $matchedProducts) {
//                if (!$matchedProducts->getPreselectHidBulbs()) {
//                    $refineFurther = true;
//                    break;
//                }
//            }
//            $collection->setSelect($originalSelect);
//        }

//        $state['action'] = $refineFurther ? $option->getAction() : 'step:done';
//        $filter->setChainState($state);


        Mage::log("Request vars:\n".print_r(Mage::app()->getRequest()->getParams(), true), null, 'trs_guide.log');
        return $this;
    }

}
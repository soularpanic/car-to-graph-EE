<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Hidsystems_Wattage
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option)
    {
        Mage::log("HID Systems Wattage Resource applying...", null, 'trs_guide.log');
        Mage::log("Option: ".print_r($option, true), null, 'trs_guide.log');
        $value = $option->getValue();
        if ($value === 'wattage_watt35') {
            $wattage = '35';
        }
        else if ($value === 'wattage_watt55') {
            $wattage = '55';
        }
        else {
            Mage::log("IDK what to do with this value: [$value]", null, 'trs_guide.log');
            return $this;
        }

        //            $collection = $filter->getLayer()->getProductCollection();
//            $suffix = 'wattage';
//            $carAlias = "car_$suffix";
//            $linkAlias = "carlink_$suffix";
//            $attrSetAlias = "attribute_set_$suffix";
//            $fitOptionAlias = "fit_option_$suffix";
//            $optionProductAlias = "option_product_$suffix";
//            $packageOptionAlias = "package_option_$suffix";
//
//            $targetAttrSet = "HID Ballasts";
//
//
//
//
//            $directFitSelect = $collection->getSelect();
//            $directFitSelect
//                ->join([$packageOptionAlias => $this->getTable('bundle/selection')],
//                    "$packageOptionAlias.parent_product_id = e.entity_id",
//                    [])
//                ->join([$optionProductAlias => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
//                    "$optionProductAlias.entity_id = $packageOptionAlias.product_id",
//                    ["preselect_$suffix" => "GROUP_CONCAT(DISTINCT $optionProductAlias.sku SEPARATOR ',')"])
//                ->join([$fitOptionAlias => $this->getTable('bundle/selection')],
//                    "$fitOptionAlias.product_id = $packageOptionAlias.product_id and $fitOptionAlias.parent_product_id != $packageOptionAlias.parent_product_id",
//                    [])
//                ->joinLeft([$attrSetAlias => 'eav_attribute_set'],
//                    "$attrSetAlias.attribute_set_id = $optionProductAlias.attribute_set_id and $attrSetAlias.attribute_set_name = '$targetAttrSet'",
//                    [])
//                ->join([$linkAlias => $this->getTable('cartographee/linkcarproduct')],
//                    "$linkAlias.product_id = $fitOptionAlias.parent_product_id",
//                    [])
//                ->join([$carAlias => $this->getTable('cartographee/car')],
//                    "$carAlias.entity_id = $linkAlias.car_id and $carAlias.alt_id = '$value'",
//                    []);
        //$targetsStmt = "('".implode("', '", $dfBundleTargets)."')";
        $_dfBundleTarget = 'HID Ballats';
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

        $collection = $filter->getLayer()->getProductCollection();
        $directFitSelect = $collection->getSelect();

        $originalSelect = clone $directFitSelect;

        $productTable = $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId();
        $sqlString = "(select
                    f.entity_id
                    ,f.sku
                    ,links.option, links.type
                    ,cars.alt_id
                from
                    $productTable as f
                    inner join eav_attribute_set as eas
                        on eas.attribute_set_id = f.attribute_set_id
                    inner join cartographee_car_product_links as links
                        on links.product_id = f.entity_id and eas.attribute_set_name = '$_dfBundleTarget'
                    inner join cartographee_cars as cars
                        on cars.entity_id = links.car_id and cars.alt_id = '$carId')";


        $step2Value = Mage::app()->getRequest()->getParam('step_2');
        $fallback = 'null';
        if ($step2Value) {
            $fallback = "'$wattage" . (in_array($step2Value, ['d2s', 'd2r']) ? "D2S" : "AMP") . "-DSP'";
        }

        $directFitSelect
            ->joinLeft([$f => new Zend_Db_Expr($sqlString)],
                "$f.entity_id = package_options.product_id",
                ["preselect_$dfBundleTarget" => "GROUP_CONCAT(DISTINCT IFNULL($f.sku, IF($bulbAlias.sku is not null, IF($bulbAlias.sku REGEXP 'D2[SR]', '{$wattage}D2S-DSP', '{$wattage}AMP-DSP') , $fallback)) SEPARATOR ',')"])
            ->orWhere("$f.sku is not null");

        $refineFurther = false;
        if (!$step2Value) {
            foreach ($collection as $matchedProducts) {
                if (!$matchedProducts->getPreselectHidBulbs()) {
                    $refineFurther = true;
                    break;
                }
            }
            $collection->setSelect($originalSelect);
        }

        $state['action'] = $refineFurther ? $option->getAction() : 'step:done';
        $filter->setChainState($state);

        Mage::log("Wattage SQL:\n{$directFitSelect->__toString()}", null, 'trs_guide.log');
        Mage::log("Request vars:\n".print_r(Mage::app()->getRequest()->getParams(), true), null, 'trs_guide.log');
        return $this;
    }

}
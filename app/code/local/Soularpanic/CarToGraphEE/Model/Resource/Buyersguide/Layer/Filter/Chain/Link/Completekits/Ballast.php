<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Completekits_Ballast
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    private $_NEXT_STEP_VAR_NAME = 'step_5';
    private $_PREV_STEP_VAR_NAME = 'step_2';

    public function applyFilterToCollection($filter, $option)
    {
//        Mage::log("completekits ballast resource starting...", null, 'trs_guide.log');
//        $skuMap = [
//            'round' => [
//                'M16.0-R' => ['MINIGAT', 'GRAPH', 'APOLLO-P', 'APOLLO-FLAT-P', 'APOLLO2-CIR-P', 'APOLLO3-P', 'ORBIT-P', 'MCR-P', 'PANA-P', 'GTI-P', 'BULLET-P', 'GG2-P', 'GAT-P', 'G37C-P', 'G37-P'],
//                'MD2S3.0-R' => ['APOLLO-P', 'APOLLO-FLAT-P', 'APOLLO2-CIR-P', 'APOLLO3-P', 'ORBIT-P', 'MCR-P', 'PANA-P', 'GTI-P', 'BULLET-P', 'G37C-P', 'G37-P'],
//                'minid2sx' => ['GG2-SQ-P'],
//                'FXR3.0-R' => ['APOLLO-P', 'APOLLO-FLAT-P', 'APOLLO2-CIR-P', 'APOLLO3-P', 'ORBIT-P', 'MCR-P', 'PANA-P', 'BULLET-P', 'G37C-P', 'G37-P'],
//                'other' => ['APOLLO-P', 'APOLLO-FLAT-P', 'APOLLO2-CIR-P', 'APOLLO3-P', 'ORBIT-P', 'MCR-P', 'PANA-P', 'BULLET-P', 'G37C-P', 'G37-P']
//            ],
//            'oval' => [
//                'M16.0-R' => ['E55-P', 'OCULAR-P'],
//                'MD2S3.0-R' => ['E55-P', 'OCULAR-P'],
//                'minid2sx' => ['APOLLO2-SQ-P', 'OCULAR-SQ-P'],
//                'FXR3.0-R' => ['E55-P', 'OCULAR-P'],
//                'other' => ['E55-P', 'OCULAR-P']
//            ],
//            'flatsided' => [
//                'M16.0-R' => ['E46-P', 'E46-EXT-P', 'IRIS-P'],
//                'MD2S3.0-R' => ['E46-P', 'E46-EXT-P', 'IRIS-P'],
//                'minid2sx' => ['APOLLO2-SQ-P', 'OCULAR-SQ-P'],
//                'FXR3.0-R' => ['E46-P', 'E46-EXT-P', 'IRIS-P'],
//                'other' => ['E46-P', 'E46-EXT-P', 'IRIS-P']
//            ],
//            'big' => [
//                'M16.0-R' => ['APOLLO-P', 'APOLLO-FLAT-P', 'APOLLO2-CIR-P', 'APOLLO3-P', 'ORBIT-P', 'MCR-P', 'PANA-P', 'BULLET-P', 'GG2-P', 'GAT-P', 'G37C-P', 'G37-P'],
//                'MD2S3.0-R' => ['APOLLO-P', 'APOLLO-FLAT-P', 'APOLLO2-CIR-P', 'APOLLO3-P', 'ORBIT-P', 'MCR-P', 'BULLET-P', 'G37C-P', 'G37-P', 'PANA-P'],
//                'minid2sx' => ['PANA-SQ-P', 'APOLLO-SQ-P', 'GG2-SQ-P', 'APOLLO2-SQ-P'],
//                'FXR3.0-R' => ['APOLLO-P', 'APOLLO-FLAT-P', 'APOLLO2-CIR-P', 'APOLLO3-P', 'ORBIT-P', 'MCR-P', 'BULLET-P', 'G37C-P', 'G37-P', 'PANA-P'],
//                'other' => ['APOLLO-P', 'APOLLO-FLAT-P', 'APOLLO2-CIR-P', 'APOLLO3-P', 'ORBIT-P', 'MCR-P', 'BULLET-P', 'G37C-P', 'G37-P', 'PANA-P']
//            ],
//            'odd' => [
//                'M16.0-R' => ['MINIGAT', 'GRAPH'],
//                'MD2S3.0-R' => ['HARDWARE:E46', 'E46-EXT-P', 'IRIS-P', 'BULLET-P'],
//                'minid2sx' => ['GG2-SQ-P', 'PANA-SQ-P'],
//                'FXR3.0-R' => ['HARDWARE:E46', 'E46-EXT-P', 'IRIS-P', 'BULLET-P'],
//                'other' => ['GG2-SQ-P', 'PANA-SQ-P']
//            ]
//        ];



        Mage::log("Kits Ballast Resource applying...", null, 'trs_guide.log');
        Mage::log("Option: ".print_r($option, true), null, 'trs_guide.log');
        $value = $option->getValue();
//        if ($value === 'tiny') {
//            $fitSkuArr = ['MATCHBOX-S'];
//        }
//        elseif ($value === 'small') {
//            $fitSkuArr = ['MATCHBOX-S', 'MH16-LH', 'MH16-RH'];
//        }
//        elseif ($value === 'medium') {
//            $fitSkuArr = ['MH16-LH', 'MH16-RH', 'MD2S-3LH', 'MD2S-3RH'];
//        }
//        elseif ($value === 'large') {
//            $fitSkuArr = ['MD2S-3LH', 'MD2S-3RH', 'FXR3-2.5LHD', 'FXR3-2.5RHD', 'FXR3-3LHD', 'FXR3-3RHD'];
//        }
//        else {
//            Mage::log("IDK what to do with this value: [$value]", null, 'trs_guide.log');
//            return $this;
//        }
//
//        if ($value) {
//            $prevSelection = Mage::app()->getRequest()->getParam($this->_PREV_STEP_VAR_NAME);
//            Mage::log("shroud shape key: $prevSelection", null, 'trs_guide.log');
//            $fitSkuArr = $skuMap[$value][$prevSelection];
//            Mage::log("shroud skus are: ".print_r($fitSkuArr, true), null, 'trs_guide.log');
//        }

        $_dfBundleTarget = 'HID Ballasts';
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

        $projectorAlias = 'f_hid_projectors';
        $nextStepValue = Mage::app()->getRequest()->getParam($this->_NEXT_STEP_VAR_NAME);
//        Mage::log("Next step value: ($nextStepValue)", null, 'trs_guide.log');
//        if (!$nextStepValue) {
//        if ($value) {
//            Mage::log("Altering SQL...", null, 'trs_guide.log');
//            $collection = $filter->getLayer()->getProductCollection();
//            $directFitSelect = $collection->getSelect();

        //$originalSelect = clone $directFitSelect;
//            $fitSkus = "('".implode("','", $fitSkuArr)."')";
//            $directFitSelect
//                ->joinLeft([$f => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
//                    "$f.entity_id = package_options.product_id and $f.sku in $fitSkus",
//                    ["preselect_$dfBundleTarget" => "GROUP_CONCAT(DISTINCT $f.sku SEPARATOR ',')"])
//                ->orWhere("$f.sku is not null");
//
//            Mage::log("Kit Ballast SQL:\n{$directFitSelect->__toString()}", null, 'trs_guide.log');
//        }

        $collection = $filter->getLayer()->getProductCollection();
        $directFitSelect = $collection->getSelect();
        if ($value) {
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

            $prevSelection = Mage::app()->getRequest()->getParam($this->_PREV_STEP_VAR_NAME);
            $fallback = 'null';
            if ($prevSelection) {
                $wattage = $value === 'wattage_watt35' ? '35' : '55';
                $fallback = $wattage . (in_array($prevSelection, ['d2s', 'd2r']) ? "D2S" : "AMP") . "-DSP";
            }
            $columnAlias = "preselect_$dfBundleTarget";
            $directFitSelect
                ->joinLeft([$f => new Zend_Db_Expr($sqlString)],
                    "$f.entity_id = package_options.product_id",
                    [$columnAlias => "GROUP_CONCAT(DISTINCT IFNULL($f.sku, IF($projectorAlias.sku is not null, IF($projectorAlias.sku REGEXP 'D2[SR]', '{$wattage}D2S-DSP', '{$wattage}AMP-DSP') , '$fallback')) SEPARATOR ',')"])
                ->orWhere("$f.sku is not null")
                ->having("$columnAlias is not null");

            Mage::log("Kit Ballast SQL:\n{$directFitSelect->__toString()}", null, 'trs_guide.log');
        }
//
//
//        $step2Value = Mage::app()->getRequest()->getParam('step_2');
//        $fallback = 'null';
//        if ($step2Value) {
//            $fallback = $wattage . (in_array($step2Value, ['d2s', 'd2r']) ? "D2S" : "AMP") . "-DSP";
//        }
//
//        $directFitSelect
//            ->joinLeft([$f => new Zend_Db_Expr($sqlString)],
//                "$f.entity_id = package_options.product_id",
//                ["preselect_$dfBundleTarget" => "GROUP_CONCAT(DISTINCT IFNULL($f.sku, IF($bulbAlias.sku is not null, IF($bulbAlias.sku REGEXP 'D2[SR]', '{$wattage}D2S-DSP', '{$wattage}AMP-DSP') , '$fallback')) SEPARATOR ',')"])
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


//        }

//        $state['action'] = $refineFurther ? $option->getAction() : 'step:done';
//        $filter->setChainState($state);


        Mage::log("Request vars:\n".print_r(Mage::app()->getRequest()->getParams(), true), null, 'trs_guide.log');
        return $this;
    }

}
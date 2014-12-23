<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Hidsystems_Wattage
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option)
    {
        Mage::log("HID Systems Wattage Resource applying...", null, 'trs_guide.log');
        Mage::log("Option: ".print_r($option, true), null, 'trs_guide.log');
        $value = $option->getValue();
        if ($value === 'wattage_watt35') {
            $collection = $filter->getLayer()->getProductCollection();
            $suffix = 'wattage';
            $carAlias = "car_$suffix";
            $linkAlias = "carlink_$suffix";
            $attrSetAlias = "attribute_set_$suffix";
            $fitOptionAlias = "fit_option_$suffix";
            $optionProductAlias = "option_product_$suffix";
            $packageOptionAlias = "package_option_$suffix";

            $targetAttrSet = "HID Ballasts";




            $directFitSelect = $collection->getSelect();
            $directFitSelect
                ->join([$packageOptionAlias => $this->getTable('bundle/selection')],
                    "$packageOptionAlias.parent_product_id = e.entity_id",
                    [])
                ->join([$optionProductAlias => $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
                    "$optionProductAlias.entity_id = $packageOptionAlias.product_id",
                    ["preselect_$suffix" => "GROUP_CONCAT(DISTINCT $optionProductAlias.sku SEPARATOR ',')"])
                ->join([$fitOptionAlias => $this->getTable('bundle/selection')],
                    "$fitOptionAlias.product_id = $packageOptionAlias.product_id and $fitOptionAlias.parent_product_id != $packageOptionAlias.parent_product_id",
                    [])
                ->join([$attrSetAlias => 'eav_attribute_set'],
                    "$attrSetAlias.attribute_set_id = $optionProductAlias.attribute_set_id and $attrSetAlias.attribute_set_name = '$targetAttrSet'",
                    [])
//                ->join([$linkAlias => $this->getTable('cartographee/linkcarproduct')],
//                    "$linkAlias.product_id = $fitOptionAlias.parent_product_id",
//                    [])
//                ->join([$carAlias => $this->getTable('cartographee/car')],
//                    "$carAlias.entity_id = $linkAlias.car_id and $carAlias.alt_id = '$value'",
//                    []);

            Mage::log("Wattage SQL:\n{$directFitSelect->__toString()}", null, 'trs_guide.log');
        }
        else if ($value === 'wattage_watt55') {

        }
        else {
            Mage::log("IDK what to do with this value: [$value]", null, 'trs_guide.log');
        }
        return $this;
    }

}
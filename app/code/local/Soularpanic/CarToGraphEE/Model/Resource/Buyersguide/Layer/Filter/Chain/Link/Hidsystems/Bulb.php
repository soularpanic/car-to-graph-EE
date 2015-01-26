<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Hidsystems_Bulb
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option)
    {
        Mage::log("its me, the bulb resource model!", null, 'trs_guide.log');
        $value = $option->getValue();
        $productTable = $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId();
        $sqlString = "(select
                        f.entity_id
                        ,cpbs.parent_product_id
                        ,f.sku
                    from
                        $productTable as f
                        inner join eav_attribute_set as eas
                            on eas.attribute_set_id = f.attribute_set_id
                                and eas.attribute_set_name = 'HID Bulbs'
                        inner join catalog_product_bundle_selection as cpbs
                            on cpbs.product_id = f.entity_id
                    where f.name like '$value:%')";

        $collection = $filter->getLayer()->getProductCollection();
        $directFitSelect = $collection->getSelect();
        $dfBundleTarget = "hid_bulbs";
        $f = "f_$dfBundleTarget";
        $directFitSelect
            ->join([$f => new Zend_Db_Expr($sqlString)],
                "$f.parent_product_id = e.entity_id",
                ["preselect_$dfBundleTarget" => "group_concat($f.sku)"]);
        Mage::log("bulb sql: ".$directFitSelect->__toString(), null, 'trs_guide.log');

        $state = $filter->getChainState();
        $state['action'] = "step:done";
        $filter->setChainState($state);
    }
}
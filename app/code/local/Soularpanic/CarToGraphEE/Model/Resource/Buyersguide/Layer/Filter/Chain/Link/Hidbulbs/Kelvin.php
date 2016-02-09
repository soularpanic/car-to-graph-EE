<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Hidbulbs_Kelvin
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Abstract {

    public function applyFilterToCollection($filter, $option)
    {
        $logger = Mage::helper('cartographee');
        $logger->log('HID Bulbs Kelvin resource starting up...');
        $value = $option->getValue();

        $_dfBundleTarget = 'HID Bulbs';
        $dfBundleTarget = strtolower(str_replace(' ', '_', $_dfBundleTarget));

        $f = "f_$dfBundleTarget";
        $state = $filter->getChainState();

        if ($value) {
            $logger->log("Altering SQL...");
            $collection = $filter->getLayer()->getProductCollection();
            $directFitSelect = $collection->getSelect();

            $likeStr = str_replace('0', '_', str_replace('K', '%', $value)).'K%';
            $columnAlias = "preselect_$dfBundleTarget";

            $catalogProductTable = $this->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId();

            $directFitSelect
                ->join(['bulb_package_options' => $this->getTable('bundle/selection')],
                    "bulb_package_options.parent_product_id = e.entity_id",
                    [])
                ->joinLeft([$f => $catalogProductTable],
                    "$f.entity_id = bulb_package_options.product_id and $f.name like '$likeStr'",
                    [$columnAlias => "GROUP_CONCAT(DISTINCT $f.entity_id SEPARATOR ',')"])
                ->where("$f.sku is not null")
                ->having("$columnAlias is not null");


            $crossreferenceTable = $filter->getCrossreference();
            if ($crossreferenceTable) {
                $crossRefRequestVar = array_keys($crossreferenceTable)[0];
                $crossRefRequestValue = Mage::app()->getRequest()->getParam($crossRefRequestVar);
                $crossRefSqlValue = $crossreferenceTable[$crossRefRequestVar][$crossRefRequestValue];

                if ($crossRefSqlValue) {
                    $subselect = Mage::getSingleton('core/resource')->getConnection('core_read')->select();
                    $subselectLinkAlias = 'subselect_link';
                    $subselectProductAlias = 'subselect_product';
                    $subselectAttributeSetAlias = 'subselect_attribute_set';
                    $subselect
                        ->from([$subselectLinkAlias => $this->getTable('cartographee/linkcarproduct')],
                            [ 'car_id' ])
                        ->join([$subselectProductAlias => $catalogProductTable],
                            "$subselectLinkAlias.product_id = $subselectProductAlias.entity_id",
                            [ 'entity_id' ])
                        ->join([$subselectAttributeSetAlias => $this->getTable('eav/attribute_set')],
                            "$subselectProductAlias.attribute_set_id = $subselectAttributeSetAlias.attribute_set_id",
                            [])
                        ->where("$subselectAttributeSetAlias.attribute_set_name = '$_dfBundleTarget'")
                        ->where("$subselectLinkAlias.type = '$crossRefSqlValue'");
                    $logger->log("\n\nSubselect SQL:\n".$subselect->__toString());

                    $subselectAlias = "subselect";
                    $directFitSelect
                        ->joinLeft([$subselectAlias => $subselect],
                            "$subselectAlias.car_id = car.entity_id",
                            [])
                        ->where("if($subselectAlias.entity_id is not null, $f.entity_id in ($subselectAlias.entity_id), true)");
                }
            }

            $logger->log("HID Bulbs Kelvin SQL:\n{$directFitSelect->__toString()}");

            $state['action'] = 'step:done';
            $filter->setChainState($state);
        }



        $logger->log("Request vars:\n".print_r(Mage::app()->getRequest()->getParams(), true));
        return $this;
    }

}

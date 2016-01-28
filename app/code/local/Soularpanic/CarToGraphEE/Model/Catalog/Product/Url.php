<?php
class Soularpanic_CarToGraphEE_Model_Catalog_Product_Url
    extends Mage_Catalog_Model_Product_Url {


    public function getUrl(Mage_Catalog_Model_Product $product, $params = array()) {
        if (!isset($params['_query'])) {
            $params['_query'] = array();
        }

        $preselect = $product->getPreselect();
        if ($preselect) {
            $preselect = implode(',', array_map('trim', explode(',', $preselect)));
            $params['_query']['preselect'] = $preselect;
            $params['_query']['carDisplay'] = $product->getData('buyers_guide_car_display');
        }

        return parent::getUrl($product, $params);
    }

}
<?php
class Soularpanic_CarToGraphEE_Model_Catalog_Product_Url
    extends Mage_Catalog_Model_Product_Url {


    public function getUrl(Mage_Catalog_Model_Product $product, $params = array()) {
        if ($product->getData('preselect')) {
            if (!isset($params['_query'])) {
                $params['_query'] = array();
            }
            $params['_query']['preselect'] = $product->getPreselect();
        }
        return parent::getUrl($product, $params);
    }

}
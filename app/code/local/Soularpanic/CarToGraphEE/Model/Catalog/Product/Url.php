<?php
class Soularpanic_CarToGraphEE_Model_Catalog_Product_Url
    extends Mage_Catalog_Model_Product_Url {


    public function getUrl(Mage_Catalog_Model_Product $product, $params = array()) {
        if (!isset($params['_query'])) {
            $params['_query'] = array();
        }

        $preselect = null;
        foreach ($product->getData() as $k => $v) {
//            Mage::log("generating url ($k/$v)", null, 'trs_guide.log');
            if (strpos($k, 'preselect') === 0) {
                $preselect = $preselect ? implode(',', [$preselect, $v]) : $v;
            }
        }

        if ($preselect) {
            $params['_query']['preselect'] = $preselect;
        }

        return parent::getUrl($product, $params);
    }

}
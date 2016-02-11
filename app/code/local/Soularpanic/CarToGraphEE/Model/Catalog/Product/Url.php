<?php
class Soularpanic_CarToGraphEE_Model_Catalog_Product_Url
    extends Mage_Catalog_Model_Product_Url {


    public function getUrl(Mage_Catalog_Model_Product $product, $params = array()) {
        if (!isset($params['_query'])) {
            $params['_query'] = array();
        }

        $preselect = null;
        foreach ($product->getData() as $k => $v) {
            /*
             * Done this way because there may be multiple data keys that start with 'preselect', e.g. bulb kelvin preselects
             */
            if (strpos($k, 'preselect') === 0) {
                $newPreselects = implode(',', array_map('trim', explode(',', $v)));
                $preselect = $preselect ? implode(',', [$preselect, $newPreselects]) : $newPreselects;
                $params['_query']['preselect'] = $preselect;
                $params['_query']['carDisplay'] = $product->getData('buyers_guide_car_display');
                $params['_query']['preselect_restrict'] = true;
            }
        }

        return parent::getUrl($product, $params);
    }

}
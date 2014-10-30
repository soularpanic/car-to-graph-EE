<?php
class Soularpanic_CarToGraphEE_Helper_Car
    extends Mage_Core_Helper_Abstract {

    private $_resolutionFailures = [];

    public function fetchCar($carArr = []) {
        $altId = $this->getCarAltId($carArr['make'], $carArr['model'], $carArr['year']);

        $car = Mage::getModel('cartographee/car')->load($altId);
        if (!$car->getId()) {
            $car->setData($carArr);
            $car->save();
        }

        return $car;
    }

    public function getCarAltId($make, $model, $year) {
        return strtolower(sprintf('%s_%s_%s', $make, $model, $year));
    }

    public function getCarProductRelations($car, $relationsRowArr) {
        $relationsData = [];
        $option = strtolower(str_replace(' ', '_', trim($relationsRowArr['option'])));
        foreach ($relationsRowArr as $relKey => $relValue) {
            if (in_array($relKey, ['make', 'model', 'year', 'option'])) {
                continue;
            }
            $type = strtolower(str_replace(' ', '_', trim($relKey)));
            foreach (explode(',', $relValue) as $relLink) {
                list($productSku, $preselectSkus) = explode(':', $relLink, 2);
                if ('-' === $productSku) {
                    continue;
                }
                $productId = $this->_resolveProduct($productSku);
                $preselectIds = $this->_resolvePreselectProducts($preselectSkus);
                if ($productId) {
                    $relationsData[] = [
                        'car_id' => $car->getId(),
                        'product_id' => $productId,
                        'preselect_ids' => $preselectIds,
                        'option' => $option,
                        'type' => $type
                    ];
                }
            }
        }
        return $relationsData;
    }

    protected function _resolveProduct($sku) {
        $_sku = trim($sku);
        if (!$_sku) {
            return false;
        }
        $product = Mage::getModel('catalog/product');
        $id = $product->getIdBySku($_sku);
        if (!$id) {
            $this->_reportResolutionFailure($_sku);
            return false;
        }
        return $id;
    }

    protected function _resolveProducts($skuArr) {
        $resolved = [];
        foreach ($skuArr as $sku) {
            $resolved[] = $this->_resolveProduct($sku);
        }
        return $resolved;
    }

    protected function _resolvePreselectProducts($preselectSkus) {
        return implode(',', $this->_resolveProducts(explode(':', $preselectSkus)));
    }

    protected function _reportResolutionFailure($sku) {
        if (in_array($sku, $this->_resolutionFailures)) {
            return;
        }
        $this->_resolutionFailures[] = $sku;
        Mage::getSingleton('adminhtml/session')->addError("Could not find product with SKU of [{$sku}]");
    }
}
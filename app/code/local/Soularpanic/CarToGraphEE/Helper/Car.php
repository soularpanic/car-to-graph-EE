<?php
class Soularpanic_CarToGraphEE_Helper_Car
    extends Soularpanic_CarToGraphEE_Helper_Data {

    public $FILTERED_CAR_PROPERTY_ORDER = ['year', 'make', 'model'];

    private $_resolutionFailures = [];


    public function fetchCar($carArr = []) {
        $_model = $carArr['option'] ? "{$carArr['model']} {$carArr['option']}" : $carArr['model'];
        $carArr['model'] = $_model;
        $altId = $this->getCarAltId($carArr['make'], $_model, $carArr['year']);

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
                        'type' => $type
                    ];
                }
            }
        }
        return $relationsData;
    }



    public function getFilteredCarProperties($properties) {
        $this->log("getFilteredCarProperties - start");
        $results = [];
        $matchedProperties = [];
        foreach ($this->FILTERED_CAR_PROPERTY_ORDER as $_propertyName) {
            $_propertyValue = $properties[$_propertyName];
            $results[$_propertyName] = $this->getFilteredCarProperty($_propertyName, $matchedProperties, $_propertyName === 'year' ? 'DESC' : 'ASC');
            if (!$_propertyValue) {
                break;
            }
            $matchedProperties[$_propertyName] = $_propertyValue;
        }

        return $results;
    }


    public function getFilteredCarProperty($propertyName, $properties = [], $order = 'ASC') {
        $cars = Mage::getModel('cartographee/car')
            ->getCollection();

        foreach ($properties as $_property => $_propertyValue) {
            if ($_propertyValue && $propertyName !== $_property) {
                $cars->addFieldToFilter($_property, $_propertyValue);
            }
        }

        $cars->getSelect()
            ->group($propertyName)
            ->order("{$propertyName} {$order}");

        return $cars->getColumnValues($propertyName);
    }

    public function getPropertiesFromRequest() {
        $carParam = Mage::app()->getRequest()->getParam('car');
        $properties = [];
        if ($carParam) {
            $car = explode('_', $carParam);
            $properties = [
                'make' => $car[0],
                'model' => $car[1],
                'year' => $car[2]
            ];
        }
        return $properties;
    }

    protected function _resolveProduct($sku) {
        $_sku = trim($sku);
        $this->log("attempting to resolve [$_sku]");
        if (!$_sku || $_sku === '-') {
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
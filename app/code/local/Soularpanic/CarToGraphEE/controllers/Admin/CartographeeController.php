<?php
class Soularpanic_CarToGraphEE_Admin_CartographeeController
    extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }


    public function resetAction() {
        $cars = Mage::getModel('cartographee/car')->getCollection();
        foreach ($cars as $scrap) {
            $scrap->delete();
        }
    }


    public function processExcelAction() {
        Mage::log('processExcelAction - start', null, 'trs_guide.log');

        $response = Mage::app()->getResponse();

        $excelPath = $this->_saveExcelFile();
        if (false === $excelPath) {
            Mage::getSingleton('adminhtml/session')->addError("Error saving spreadsheet.  Import aborted.");
            $response->setHttpResponseCode(500);
            $response->setBody(Mage::helper('core')->jsonEncode([
                'errors' => true,
                'message' => 'Error saving spreadsheet'
            ]));
            return $this;
        }

        $excelHelper = Mage::helper('cartographee/excel');
        $carHelper = Mage::helper('cartographee/car');
        $data = $excelHelper->parseExcel($excelPath);

        $lastMake = null;
        foreach ($data as $relationRow) {

            $make = $relationRow['make'] ?: $lastMake;
            $lastMake = $make;
            $relationRow['make'] = $make;

            $car = $carHelper->fetchCar($relationRow);

            $relations = $carHelper->getCarProductRelations($car, $relationRow);
            //$excelHelper->log(print_r($relations, true));
            Mage::log("beginning loop", null, 'trs_guide.log');
            foreach ($relations as $relation) {
                $link = Mage::getModel('cartographee/linkcarproduct');
                $link->setData($relation);
                if ($link->getOption() === 'bundled_product') {
                    $linkCollection = Mage::getModel('cartographee/linkcarproduct')
                        ->getCollection()
                        ->addFieldToFilter('car_id', ['eq' => $link->getCarId()])
                        ->addFieldToFilter('product_id', ['eq' => $link->getProductId()])
                        ->addFieldToFilter('`option`', ['eq' => 'bundled_product']);
//                    $carHelper->log("sql: ".$linkCollection->getSelect()->__toString());
                    if ($linkCollection->getSize() <= 0) {
                        $carHelper->log("{$link->getCarId()}/{$link->getProductId()} does not exist; linking");
                        $link->save();
                    }
                    else {
                        $carHelper->log("{$link->getCarId()}/{$link->getProductId()} already exists; skipping");
                    }
                }
                else {
                    $link->save();
                }

//                $bundleProduct = Mage::getModel('catalog/product')
//                    ->load($link->getProductId());
//                if ($bundleProduct->getTypeId() === 'bundle') {
//                    $kids = $bundleProduct->getTypeInstance(true)
//                        ->getSelectionsCollection(
//                            $bundleProduct->getTypeInstance(true)
//                                ->getOptionsIds($bundleProduct), $bundleProduct);
//
////                $bundleProduct
////                    ->getTypeInstance(true)
////                    ->getChildrenIds($bundleProduct->getId(), false);
//                    //$kids = $bundleProduct->getSelectionsCollection();
//                    $excelHelper->log("printing kids...");
//                    foreach ($kids as $kid) {
//                        $excelHelper->log("I am a kid! ({$kid->getId()})");
//                        $sublink = Mage::getModel('cartographee/linkcarproduct');
//                        $sublink->setData($relation);
//                        $sublink->save();
//                    }
                //Mage::log("kids:".print_r($bundleProduct, true), null, 'trs_guide.log');
//                }
            }
        }
        return $this;
    }


    protected function _saveExcelFile() {
        Mage::log("saving excel file...", null, 'trs_guide.log');
        $requestKey = Mage::helper('cartographee/excel')->getUploadElementName();
        $filename = $_FILES[$requestKey]['name'];
        if ($filename) {
            $uploader = new Varien_File_Uploader($requestKey);
            $uploader->setAllowedExtensions(['xls'])
                ->setAllowRenameFiles(false)
                ->setFilesDispersion(false);
            $path = Mage::getBaseDir('var').DS.'tmp'.DS;
            Mage::log("writing to disk...", null, 'trs_guide.log');
            try {
                $uploader->save($path, $filename);
                return $path.$filename;
            }
            catch (Exception $e) {
                Mage::log("FAILED!~ {$e->getMessage()}", null, 'trs_guide');
                Mage::getSingleton('adminhtml/session')->addError("Error saving {$filename}: {$e->getMessage()}");
                return false;
            }
        }
        return false;
    }
}
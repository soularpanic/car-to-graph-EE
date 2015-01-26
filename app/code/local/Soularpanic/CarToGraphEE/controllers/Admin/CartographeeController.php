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
            Mage::log("beginning loop", null, 'trs_guide.log');
            foreach ($relations as $relation) {

                Mage::log("relation: (".print_r($relation, true).")", null, 'trs_guide.log');

                $link = Mage::getModel('cartographee/linkcarproduct');
                $link->setData($relation);
                $link->save();
            }
        }
        return $this;
    }


    protected function _saveExcelFile() {
        Mage::log("saving excel file...", null, 'trs_guide.log');
        $requestKey = Mage::helper('cartographee/excel')->getUploadElementName();
        $filename = Varien_File_Uploader::getCorrectFileName($_FILES[$requestKey]['name']);
        Mage::log("filename: $filename", null, 'trs_guide.log');
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
                Mage::log("FAILED!~ {$e->getMessage()}", null, 'trs_guide.log');
                Mage::getSingleton('adminhtml/session')->addError("Error saving {$filename}: {$e->getMessage()}");
                return false;
            }
        }
        return false;
    }
}
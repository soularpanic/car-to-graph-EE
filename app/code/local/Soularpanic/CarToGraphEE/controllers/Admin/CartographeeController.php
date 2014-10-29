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
            $excelHelper->log(print_r($relations, true));
        }
//        $parser->setData($data);
//        $parsed = $parser->parse();
//        $fp = fopen(Mage::getBaseDir('var').'/tmp/Sizing.xls', 'rb');
//        if ($fp === FALSE) {
//            Mage::log('Could not open file to parse', null, 'trs_guide.log');
//        }
//        else {
//            $data = fread($fp, filesize($fp));
//            $parser->setData($data);
//        }
//        fclose($fp);
        return 'hey';
    }


    protected function _saveExcelFile() {
        $requestKey = Mage::helper('cartographee/excel')->getUploadElementName();
        $filename = $_FILES[$requestKey]['name'];
        if ($filename) {
            $uploader = new Varien_File_Uploader($requestKey);
            $uploader->setAllowedExtensions(['xls'])
                ->setAllowRenameFiles(false)
                ->setFilesDispersion(false);
            $path = Mage::getBaseDir('var').DS.'tmp'.DS;
            try {
                $uploader->save($path, $filename);
                return $path.$filename;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError("Error saving {$filename}: {$e->getMessage()}");
                return false;
            }
        }
        return false;
    }
}
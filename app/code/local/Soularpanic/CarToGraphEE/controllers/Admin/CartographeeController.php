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
        $logger = Mage::helper('cartographee');
        $logger->log('processExcelAction - start');

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
        $excelObj = $excelHelper->getRawExcel($excelPath);

        $lastMake = null;

        foreach ($excelObj->getAllSheets() as $worksheet) {
            $logger->log("Processing worksheet [{$worksheet->getTitle()}]");
            $lastRow = $worksheet->getHighestDataRow();
            $logger->log("got last row ($lastRow)");
            $lastCol = $worksheet->getHighestDataColumn();
            $logger->log("got last col ($lastCol)");
            $keyRow = [];
            $logger->log("beginning to iterate through sheet...");
            for ($row = 1; $row <= $lastRow; $row++) {
                $relation = [];
                $logger->log("key row ($row): ".print_r($keyRow,true));
                for ($col = 'A'; $col != $lastCol; $col++) {
                    if ($worksheet->cellExists($col.$row)) {
                        $cell = $worksheet->getCell($col.$row);
                        if ($row === 1) {
                            $keyRow[$col] = str_replace(':', '', strtolower($cell->getValue()));
                        }
                        else {
                            $key = $keyRow[$col];
                            $relation[$key] = $cell->getValue();
                        }
                    }
                }
                if ($relation) {
                    $relations[] = $relation;

                    $_relationRow = $relation;
                    $make = $_relationRow['make'] ?: $lastMake;
                    $lastMake = $make;
                    $_relationRow['make'] = $make;

                    $car = $carHelper->fetchCar($_relationRow);
                    $_relations = $carHelper->getCarProductRelations($car, $_relationRow);
                    foreach ($_relations as $_relation) {

                        $logger->log("relation: (".print_r($_relation, true).")");

                        $link = Mage::getModel('cartographee/linkcarproduct');
                        $link->setData($_relation);
                        $link->save();
                    }
                }
            }
        }


        return $this;
    }


    protected function _saveExcelFile() {
        $logger = Mage::helper('cartographee');
        $logger->log("saving excel file...");
        $requestKey = Mage::helper('cartographee/excel')->getUploadElementName();
        $filename = Varien_File_Uploader::getCorrectFileName($_FILES[$requestKey]['name']);
        $logger->log("filename: $filename");
        if ($filename) {
            $uploader = new Varien_File_Uploader($requestKey);
            $uploader->setAllowedExtensions(['xls'])
                ->setAllowRenameFiles(false)
                ->setFilesDispersion(false);
            $path = Mage::getBaseDir('var').DS.'tmp'.DS;
            $logger->log("writing to disk...");
            try {
                $uploader->save($path, $filename);
                return $path.$filename;
            }
            catch (Exception $e) {
                $logger->log("FAILED!~ {$e->getMessage()}");
                Mage::getSingleton('adminhtml/session')->addError("Error saving {$filename}: {$e->getMessage()}");
                return false;
            }
        }
        return false;
    }
}
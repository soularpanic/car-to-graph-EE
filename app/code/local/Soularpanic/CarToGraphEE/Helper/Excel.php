<?php
class Soularpanic_CarToGraphEE_Helper_Excel
    extends Mage_Core_Helper_Abstract {

    const UPLOAD_ELEMENT_NAME = 'excelUpload';

    function __construct() {
        require_once(Mage::getBaseDir('lib').'/PHPExcel/PHPExcel.php');
    }


    public function log($message) {
        Mage::log($message, null, 'trs_guide.log');
        return $this;
    }

    public function getUploadElementName() {
        return self::UPLOAD_ELEMENT_NAME;
    }

    public function parseExcel($path) {
        $this->log('Beginning helper method...');
        $excelObj = PHPExcel_IOFactory::load($path);
        $this->log('XLS parsed; processing...');
        $relations = [];
        foreach ($excelObj->getAllSheets() as $worksheet) {
            $this->log("Processing worksheet [{$worksheet->getTitle()}]");
            $lastRow = $worksheet->getHighestDataRow();
            $lastCol = $worksheet->getHighestDataColumn();
            $keyRow = [];
            for ($row = 1; $row <= $lastRow; $row++) {
                $relation = [];
                for ($col = 'A'; $col <= $lastCol; $col++) {
                    $cell = $worksheet->getCell($col.$row);
                    if ($row === 1) {
                        $keyRow[$col] = strtolower($cell->getValue());
                    }
                    else {
                        $key = $keyRow[$col];
                        $relation[$key] = $cell->getValue();
                    }
                }
                if ($relation) {
                    $relations[] = $relation;
                }
            }
        }
        return $relations;
    }
}
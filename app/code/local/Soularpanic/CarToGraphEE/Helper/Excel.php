<?php
class Soularpanic_CarToGraphEE_Helper_Excel
    extends Mage_Core_Helper_Abstract {

    const UPLOAD_ELEMENT_NAME = 'excelUpload';

    function __construct() {
        require_once(Mage::getBaseDir('lib').'/PHPExcel/PHPExcel.php');

        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3);
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
            $this->log("got last row");
            $lastCol = $worksheet->getHighestDataColumn();
            $this->log("got last col");
            $keyRow = [];
            $this->log("beginning to iterate through sheet...");
            for ($row = 1; $row <= $lastRow; $row++) {
                $relation = [];
                for ($col = 'A'; $col <= $lastCol; $col++) {
                    $cell = $worksheet->getCell($col.$row);
                    if ($row === 1) {
                        $keyRow[$col] = str_replace(':', '', strtolower($cell->getValue()));
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
        $this->log("returning relations");
        return $relations;
    }
}
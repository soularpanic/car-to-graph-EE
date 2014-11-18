<?php
class Soularpanic_CarToGraphEE_Model_Resource_Catalog_Product_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection {


    public function setSelect($select) {
        $this->_select = $select;
    }

}
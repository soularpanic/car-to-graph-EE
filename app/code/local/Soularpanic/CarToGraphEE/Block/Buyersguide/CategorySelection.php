<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_CategorySelection
    extends Mage_Core_Block_Template {

    public function getCategories() {
        $leaves = [];
        $rootCategory = Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId());
        $leaves = $this->_getLeaves($rootCategory, $leaves);
        return $leaves;
    }

    protected function _getLeaves($category, &$leaves) {
        if ($category->hasChildren()) {
            $children = $category->getChildrenCategories();
            foreach ($children as $child) {
                $this->_getLeaves($child, $leaves);
            }
        }
        else {
            $leaves[] = $category;
        }
        return $leaves;
    }
}
<?php
class Soularpanic_CarToGraphEE_Model_Resource_Catalog_Product_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection {


    public function setSelect($select) {
        $this->_select = $select;
    }

    public function getProductCountSelect() {
        if ($this->_productCountSelect === null) {
            $pcSelect = clone $this->getSelect();

            if (!$pcSelect->getPart(Zend_Db_Select::HAVING)) {
                $pcSelect->reset(Zend_Db_Select::COLUMNS);
            }

            $pcSelect->reset(Zend_Db_Select::GROUP)
                ->reset(Zend_Db_Select::ORDER)
                ->distinct(false)
                ->join(array('count_table' => $this->getTable('catalog/category_product_index')),
                    'count_table.product_id = e.entity_id',
                    array(
                        'count_table.category_id',
                        'product_count' => new Zend_Db_Expr('COUNT(DISTINCT count_table.product_id)')
                    )
                )
                ->where('count_table.store_id = ?', $this->getStoreId())
                ->group('count_table.category_id');

            $this->_productCountSelect = $pcSelect;
        }

        return $this->_productCountSelect;
    }

//
//    public function addCountToCategories($categoryCollection) {
//        $isAnchor    = array();
//        $isNotAnchor = array();
//        foreach ($categoryCollection as $category) {
//            if ($category->getIsAnchor()) {
//                $isAnchor[]    = $category->getId();
//            } else {
//                $isNotAnchor[] = $category->getId();
//            }
//        }
//        $productCounts = array();
//        if ($isAnchor || $isNotAnchor) {
//            $select = $this->getProductCountSelect();
//
//            Mage::dispatchEvent(
//                'catalog_product_collection_before_add_count_to_categories',
//                array('collection' => $this)
//            );
//
//            $columns = $select->getPart(Zend_Db_Select::COLUMNS);
//            $distincts = [];
//            foreach ($columns as $column) {
//                $_columnRef = $column[2] ?: $column[1]; // 2 is alias, if set; 1 is name/expr
//                if (!in_array($_columnRef, $distincts)) {
//                    $distincts[] = $_columnRef;
//                }
//            }
//
//            if ($isAnchor) {
//                $anchorStmt = $this->getConnection()->select();
//                $anchorStmt->from(clone $select, $distincts);
//                $anchorStmt->limit(); //reset limits
//                $anchorStmt->where('count_table.category_id IN (?)', $isAnchor);
//                $productCounts += $this->getConnection()->fetchPairs($anchorStmt);
//                $anchorStmt = null;
//            }
//            if ($isNotAnchor) {
//                $notAnchorStmt = $this->getConnection()->select();
//                $notAnchorStmt->from(['wrap' => clone $select],
//                    $distincts);
//                $notAnchorStmt->limit(); //reset limits
//                $notAnchorStmt->where('count_table.category_id IN (?)', $isNotAnchor);
//                $notAnchorStmt->where('count_table.is_parent = 1');
//                $productCounts += $this->getConnection()->fetchPairs($notAnchorStmt);
//                $notAnchorStmt = null;
//            }
//            $select = null;
//            $this->unsProductCountSelect();
//        }
//
//        foreach ($categoryCollection as $category) {
//            $_count = 0;
//            if (isset($productCounts[$category->getId()])) {
//                $_count = $productCounts[$category->getId()];
//            }
//            $category->setProductCount($_count);
//        }
//
//        return $this;
//    }


    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();
        $countSelect = (is_null($select)) ?
            $this->_getClearSelect() :
            $this->_buildClearSelect($select);
        // Clear GROUP condition for count method
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->reset(Zend_Db_Select::HAVING);
        $countSelect->columns('COUNT(DISTINCT e.entity_id)');
        if ($resetLeftJoins) {
            $countSelect->resetJoinLeft();
        }
        return $countSelect;
    }

}
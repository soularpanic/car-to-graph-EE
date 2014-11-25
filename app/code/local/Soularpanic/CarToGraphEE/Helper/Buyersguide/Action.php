<?php
class Soularpanic_CarToGraphEE_Helper_Buyersguide_Action
    extends Soularpanic_CarToGraphEE_Helper_Data {


    public function getCarLinkTableAlias() {
        return 'carlink';
    }


    public function isTerminal($actionStr) {
        return strpos($actionStr, 'sku:') === 0;
    }


    public function applyActionToCollection($filter, $actionsStr) {
        $this->log('applying action to collection - start');
        $actions = explode(';', $actionsStr);
        foreach ($actions as $actionStr) {
            list($action, $value) = explode(':', $actionStr, 2);
            $action = trim($action);
            $value = trim($value);
            $this->log("action: [{$action}]; value: [{$value}]");
            if ($action == 'sku') {
                $this->_applySkuToCollection($filter, $value);
            }
            elseif ($action == 'step') {
                $this->_applyStepToCollection($filter, $value);
            }
            elseif ($action == 'sql') {
                $this->_applySqlToCollection($filter, $value);
            }
            else {
                $this->log("Unhandled action: [{$action}]/[{$value}]", null, 'trs_guide.log');
            }
        }
    }

    protected function _applySkuToCollection($filter, $skuAction) {
        $this->log("applying sku action");
        $matches = [];
        $matched = preg_match('/^([^\[]+)(?:\[([^\]]+)\])?$/', $skuAction, $matches);

        if ($matched) {
            $this->log("sku matches: ".print_r($matches, true));
            $select = $filter->getLayer()->getProductCollection()->getSelect();
            $sku = $matches[1];
            $select->where("e.sku = '{$sku}'");

            if (count($matches) > 1) {
                $preselector = $matches[2];
                $select->joinLeft(new Zend_Db_Expr("(select '{$sku}' as sku, '{$preselector}' as preselect)"),
                    "e.sku = '{$sku}'",
                    ['preselect']
                );
            }

            $this->log("sku SQL:\n".$select->__toString());
        }
    }


    protected function _applyStepToCollection($filter, $step) {
        $this->log("checking step value ({$step}) for sku filters...");
        $collection = $filter->getLayer()->getProductCollection();
        $matches = [];
        $matched = preg_match('/^\d+\[([^\]]+)\]/', $step, $matches);
        if ($matched) {
            $this->log("sku filter matches: ".print_r($matches, true));
            $skusRawStr = $matches[1];
            $skus = implode("', '", array_map("trim", explode(',', $skusRawStr)));
            $collection->getSelect()->where("e.sku IN ('{$skus}')");
            $this->log("collection sql: ".$collection->getSelect()->__toString());
        }
    }


    protected function _applySqlToCollection($filter, $field) {
        $this->log("field: [{$field}]");
        $collection = $filter->getLayer()->getProductCollection();
        $matches = [];
        $matched = preg_match('/^([^=]+)=(.+)$/', $field, $matches);
        if ($matched) {
            $tableAlias = $this->getCarLinkTableAlias();
            $column = $matches[1];
            $value = $matches[2];
            $collection->getSelect()->where("{$tableAlias}.{$column} = '{$value}'");
            $this->log("collection sql: ".$collection->getSelect()->__toString());
        }
    }
}
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
                $this->_applySqlToCollection($filter, $value, false);
            }
            elseif ($action == 'fit_sql') {
                $this->_applySqlToCollection($filter, $value, true);
            }
            elseif (strpos($action, '(') === 0) {
                $this->_applyComplex($filter, $action);
            }
            else {
                $this->log("Unhandled action: [{$action}]/[{$value}]", null, 'trs_guide.log');
            }
        }
    }


    protected function _applyComplex($filter, $complex) {
        $matches = [];
        $matched = preg_match('/^\((get|set) ([^\)]+)\)$/', $complex, $matches);

        if (!$matched) {
            $this->log("Could not parse complex -{$complex}-");
            return false;
        }

        $command = $matches[1];
        $remainder = trim($matches[2]);
        if ($command === "set") {
            return $this->_setChainVar($filter, $remainder);
        }
        if ($command == "get") {
            return $this->_getChainVar($filter, $remainder);
        }
        return false;
    }


    protected function _setChainVar($filter, $command) {
        list($name, $val) = explode('=', $command);
        $name = trim($name);
        $val = trim($val);
        $chainState = $filter->getChainState();
        $chainState[$name] = $val;
        $filter->setChainState($chainState);
        return;
    }


    protected function _getChainVar($filter, $varName) {
        $this->log("attempting to fetch [$varName] from chain");
        $chainState = $filter->getChainState();
        $val = $chainState[$varName];
        $this->log("fetched [$val]");
        return $val;
    }


    protected function _applySkuToCollection($filter, $skuAction) {
        $this->log("applying sku action");

        $skuPairs = explode(',', $skuAction);

        $skuPreselects = [];

        foreach ($skuPairs as $skuPair) {
            $matches = [];
            $matched = preg_match('/^([^\[]+)(?:\[([^\]]+)\])?$/', trim($skuPair), $matches);

            if ($matched) {
                $sku = $matches[1];
                $preselect = count($matches > 1) ? $matches[2] : '';
                $skuPreselects[$sku] = $preselect;
            }
        }

        $skuInStmt = "e.sku in ('" . implode("', '", array_keys($skuPreselects)) . "')";

        $preselectStmtArr = array_map(
            function($sku, $preselector) { return "select '{$sku}' as sku, '{$preselector}' as preselect"; },
            array_keys($skuPreselects),
            $skuPreselects
        );

        $preselectStmt = '('.implode(' union ', $preselectStmtArr).')';

        $select = $filter->getLayer()->getProductCollection()->getSelect();
        $select
            ->joinLeft(['preselects' => new Zend_Db_Expr($preselectStmt)],
                "e.sku = preselects.sku",
                ['preselect'])
            ->where($skuInStmt);

        $this->log("sku SQL:\n".$select->__toString());
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


    protected function _applySqlToCollection($filter, $field, $shouldWriteAction = false) {
        $this->log("field: [{$field}]");
        $collection = $filter->getLayer()->getProductCollection();
        $select = $collection->getSelect();
        $tables = $select->getPart(Zend_Db_Select::FROM);

        if (!array_key_exists($this->getCarLinkTableAlias(), $tables)) {
            return;
        }

        $matches = [];
        $matched = preg_match('/^([^=]+)=(.+)$/', $field, $matches);
        if ($matched) {
            $tableAlias = $this->getCarLinkTableAlias();
            $column = $matches[1];
            $value = $matches[2];
            $select->where("{$tableAlias}.{$column} = '{$value}'");
            $this->log("collection sql: ".$select->__toString());
            $fits = $collection->count();
            $collection->clear();
            $state = $filter->getChainState();
            $state['has_direct_fit'] = $fits;

            if ($shouldWriteAction) {
                $state['action'] = $fits ? 'step:directfit' : 'step:nofit';
            }

            $filter->setChainState($state);
            $this->log("After supplemental application, chain state: ".print_r($state, true));
        }
    }
}
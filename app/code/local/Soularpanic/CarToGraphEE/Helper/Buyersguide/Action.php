<?php
class Soularpanic_CarToGraphEE_Helper_Buyersguide_Action
    extends Soularpanic_CarToGraphEE_Helper_Data {


    public function getCarLinkTableAlias() {
        return 'carlink';
    }


    public function isTerminal($actionStr) {
        return (strpos($actionStr, 'sku:') === 0 || strpos($actionStr, 'product_id:') === 0);
    }


    public function applyActionToCollection($filter, $actionsStr) {
        $this->log('applying action to collection - start');
        $actions = explode(';', $actionsStr);
        $toReturn = true;
        foreach ($actions as $actionStr) {
            list($action, $value) = explode(':', $actionStr, 2);
            $action = trim($action);
            $value = trim($value);
            $this->log("action: [{$action}]; value: [{$value}]");
            if ($action == 'sku') {
                $toReturn &= $this->_applySkuToCollection($filter, $value);
            }
            if ($action == 'product_id') {
                $toReturn &= $this->_applyIdToCollection($filter, $value);
            }
            elseif ($action == 'step') {
                $toReturn &= $this->_applyStepToCollection($filter, $value);
            }
            elseif ($action == 'sql') {
//                return $this->_applySqlToCollection($filter, $value, false);
                $toReturn &= $this->_applySqlToCollection($filter, $value, "step:next");
            }
            elseif ($action == 'fit_sql') {
//                return $this->_applySqlToCollection($filter, $value, true);
                $toReturn &= $this->_applySqlToCollection($filter, $value, "step:directfit");
            }
            elseif (strpos($action, 'preselect_id') === 0) {
                $toReturn &= $this->_applyPreselectIdToCollection($filter, $action, $value);
            }
            elseif ($action == 'remove_preselect') {
                $toReturn &= $this->_applyRemovePreselectToCollection($filter, $value);
            }
            elseif (strpos($action, 'set') === 0) {
                $toReturn &= $this->_applySetToRequest($action, $value);
            }
            elseif (strpos($action, '(') === 0) {
                $toReturn &= $this->_applyComplex($filter, $action);
            }
            elseif ($action == 'done') {
                return false;
            }
            else {
                $this->log("Unhandled action: [{$action}]/[{$value}]", null, 'trs_guide.log');
                return false;
            }
        }
        return $toReturn;
    }


    public function toDirectFitTableAlias($targetName) {
        return "f_".strtolower(str_replace(' ', '_', $targetName));
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
        return false;
    }

    protected function _applyIdToCollection($filter, $idAction) {
        $this->log("applying id action");

        $idPairs = explode(',', $idAction);

        /*
         * Split action string up into id[preselect ids] strings
         */
        $actionRemainder = trim($idAction);
        $remainderMatch = true;
        $actionMatches = [];
        $actionMatch = [];
        while($remainderMatch && strlen($actionRemainder)) {
            $remainderMatch = preg_match('(^(\d+(?:\[[\d\s,]+\])?)[\s,]*)', $actionRemainder, $actionMatch);
            if ($remainderMatch) {
                $actionMatches[] = $actionMatch[1];
                $actionRemainder = substr($actionRemainder, strlen($actionMatch[0]));
            }
        }



        $idPreselects = [];
        $sqlNeedsPreselect = false;

        foreach($actionMatches as $idPair) {
            $idMatches = [];
            $idMatched = preg_match('((\d+)(?:\[([\d, ]+)\])?)', $idPair, $idMatches);
            if ($idMatched) {
                $id = $idMatches[1];
                $preselect = count($idMatches) > 1 ? $idMatches[2] : '';
                if ($preselect) {
                    $sqlNeedsPreselect = true;
                }
                $idPreselects[$id] = $preselect;
            }
        }
//        foreach ($idPairs as $idPair) {
//            $matches = [];
//            $matched = preg_match('/^([^\[]+)(?:\[([^\]]+)\])?$/', trim($idPair), $matches);
//
//            if ($matched) {
//                $id = $matches[1];
//                $preselect = count($matches > 1) ? $matches[2] : '';
//                $idPreselects[$id] = $preselect;
//            }
//        }

        $idInStmt = "e.entity_id in ('" . implode("', '", array_keys($idPreselects)) . "')";

        $preselectStmtArr = array_map(
            function($id, $preselector) { return "select '{$id}' as product_id, '{$preselector}' as preselect"; },
            array_keys($idPreselects),
            $idPreselects
        );

        $preselectStmt = '('.implode(' union ', $preselectStmtArr).')';

        $select = $filter->getLayer()->getProductCollection()->getSelect();
        /*
         * Can only use preselects once w/o table naming conflict; YAGNI
         */
        if ($sqlNeedsPreselect) {
            $select
                ->joinLeft(['preselects' => new Zend_Db_Expr($preselectStmt)],
                    "e.entity_id = preselects.product_id",
                    ['preselect']);
        }
        $select->where($idInStmt);

        $this->log("id SQL:\n".$select->__toString());
        return false;
    }


    protected function _applyStepToCollection($filter, $step) {
//        $this->log("checking step value ({$step}) for sku filters...");
//        $collection = $filter->getLayer()->getProductCollection();
//        $matches = [];
//        $matched = preg_match('/^\d+\[([^\]]+)\]/', $step, $matches);
//        if ($matched) {
//            $this->log("sku filter matches: ".print_r($matches, true));
//            $skusRawStr = $matches[1];
//            $skus = implode("', '", array_map("trim", explode(';', $skusRawStr)));
//            $collection->getSelect()->where("e.sku IN ('{$skus}')");
//            $this->log("collection sql: ".$collection->getSelect()->__toString());
//        }
        return true;
    }


    protected function _applySqlToCollection($filter, $field, $directFitAction /* $shouldWriteAction = false */) {
        $this->log("field: [{$field}]");
        $collection = $filter->getLayer()->getProductCollection();
        $select = $collection->getSelect();
        $tables = $select->getPart(Zend_Db_Select::FROM);

        $targetName = $filter->getDirectFitBundleTarget() ? $this->toDirectFitTableAlias($filter->getDirectFitBundleTarget()) : $this->getCarLinkTableAlias();

        if (!array_key_exists($targetName, $tables)) {
            return;
        }

        $matches = [];
        $matched = preg_match('/^([^=]+)=(.+)$/', $field, $matches);
        if ($matched) {
            $tableAlias = $targetName;
            $column = $matches[1];
            $value = $matches[2];
            $select->where("{$tableAlias}.{$column} = '{$value}'");
            $this->log("collection sql:\n ".$select->__toString());
            $fits = $collection->count();
            $collection->clear();
            $state = $filter->getChainState();
            $state['has_direct_fit'] = $fits;


            $state['action'] = $fits ? $directFitAction : 'step:nofit';
//            if ($shouldWriteAction) {
//                $state['action'] = $fits ? 'step:directfit' : 'step:nofit';
//            }

            $filter->setChainState($state);
            $this->log("After supplemental application, chain state: ".print_r($state, true));
        }
        return true;
    }

    protected function _applyPreselectIdToCollection($filter, $action, $value) {

        $this->log("Altering SQL for preselect...");

        $dfBundleTarget = substr($action, strlen("preselect_id_"));
        $f = "f_$dfBundleTarget";
        $collection = $filter->getLayer()->getProductCollection();
        $directFitSelect = $collection->getSelect();

        //$originalSelect = clone $directFitSelect;
        $fitIds = "('".implode("','", array_map('trim', explode(',', $value)))."')";
        $columnAlias = "preselect_$dfBundleTarget";
        $directFitSelect
            ->joinLeft([$f => $collection->getResource()->getTable('catalog/product_flat').'_'.Mage::app()->getStore()->getStoreId()],
                "$f.entity_id = package_options.product_id and $f.entity_id in $fitIds",
                [$columnAlias => "GROUP_CONCAT(DISTINCT $f.entity_id SEPARATOR ',')"])
            ->orWhere("$f.sku is not null")
            ->having("$columnAlias is not null");

        $this->log("Preselect SQL:\n{$directFitSelect->__toString()}");
        return true;
    }

    protected function _applyRemovePreselectToCollection($filter, $value) {
        $preselectKey = "preselect_$value";

        $select = $filter->getLayer()->getProductCollection()->getSelect();
        $columns = $select->getPart('columns');
        $having = $select->getPart('having');

        $select->reset('columns');
        foreach ($columns as $k => $v) {
            if ($v[2] != $preselectKey) {
                $select->columns([$v[2] => $v[1]], $v[0]);
            }
        }

        $select->reset('having');
        foreach ($having as $k => $v) {
            if (strpos($v, $preselectKey) === false) {
                $select->having($v);
            }
        }

        return true;
    }

    protected function _applySetToRequest($action, $value) {
        $key = substr($action, strlen('set_'));
        Mage::app()->getRequest()->setParam($key, $value);
        return true;
    }

}
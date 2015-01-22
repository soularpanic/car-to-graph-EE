<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Completekits_Harness
    extends Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Chain_Link_Configurable {

    public function applyFilterToCollection($filter, $option) {
        $action = $option->getAction();

        if (!$action) {
            return true;
        }

        $whitelistStr = $filter->getSkuRestraint();
        if ($whitelistStr) {
            $whitelistSql = "'".implode("', '", array_map('trim', explode(',', $whitelistStr)))."'";
            $collection = $filter->getLayer()->getProductCollection();
            $collection->getSelect()
                ->where("e.sku IN ($whitelistSql)");
        }

        Mage::helper('cartographee/buyersguide_action')->applyActionToCollection($filter, $action);

        $chainState = $filter->getChainState();
        $chainState['action'] = $chainState['has_direct_fit'] > 0 ? 'step:next' : 'step:nofit';
        $filter->setChainState($chainState);
        return $this;
    }
}
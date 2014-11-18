<?php
class Soularpanic_CarToGraphEE_Model_Resource_Buyersguide_Layer_Filter_Car
    extends Mage_Core_Model_Resource_Db_Abstract {

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init('cartographee/car', 'entity_id');
    }


    public function applyFilterToCollection($filter, $value) {
        Mage::log('applying car filter in resource!', null, 'trs_guide.log');
        $collection = $filter->getLayer()->getProductCollection();

        $directFitSelect = $collection->getSelect();
        $originalSelect = clone $directFitSelect;

        $carAlias = 'car';
        $linkAlias = 'carlink';
        $linkTable = $this->getTable('cartographee/linkcarproduct');
        $directFitSelect
            ->join(
                [$linkAlias => $linkTable],
                "{$linkAlias}.product_id = e.entity_id",
                []
            )
            ->join(
                [$carAlias => $this->getMainTable()],
                "{$carAlias}.alt_id = '{$value}' and {$carAlias}.entity_id = {$linkAlias}.car_id",
                []
            )
            ->group('e.entity_id');

        $directFits = $collection->count();
        Mage::log("Direct fits found: [{$directFits}]", null, 'trs_guide.log');
        if ($directFits <= 0) {
            Mage::log("Restoring original select to collection", null, 'trs_guide.log');
            $collection->clear();
            $collection->setSelect($originalSelect);
        }

        Mage::log("filter? ".get_class($filter), null, 'trs_guide.log');
        $chain = $filter->getChain();
        Mage::log("chain? ".get_class($chain), null, 'trs_guide.log');
        $state = $filter->getChainState();
        $state['has_direct_fit'] = $directFits;// > 0 ? true : false;
        Mage::log("in car resource, chain state: [".print_r($state, true)."]", null, 'trs_guide.log');
        $filter->setChainState($state);

        return $this;
    }
}
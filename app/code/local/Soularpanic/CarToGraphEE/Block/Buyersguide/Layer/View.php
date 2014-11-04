<?php
class Soularpanic_CarToGraphEE_Block_Buyersguide_Layer_View
    extends Mage_Core_Block_Template
{
    /**
     * State block name
     *
     * @var string
     */
    protected $_stateBlockName;

    /**
     * Category Block Name
     *
     * @var string
     */
    protected $_categoryBlockName;

    /**
     * Attribute Filter Block Name
     *
     * @var string
     */
    protected $_attributeFilterBlockName;

    /**
     * Price Filter Block Name
     *
     * @var string
     */
    protected $_priceFilterBlockName;

    /**
     * Decimal Filter Block Name
     *
     * @var string
     */
    protected $_decimalFilterBlockName;

    protected $_buyersguideFilterBlockName;

    protected $_whitelistedAttributeNames;
    protected $_excludedAttributeNames;

    /**
     * Internal constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_excludedAttributeNames = array();
        $this->_whitelistedAttributeNames = array();
        $this->_initBlocks();
    }

    public function excludeAttribute($attributeName) {

        if (!in_array($attributeName, $this->$_excludedAttributeNames)) {
            $this->_excludedAttributeNames[] = $attributeName;
        }
    }

    public function whitelistAttribute($attributeCode) {
        if (!in_array($attributeCode, $this->_whitelistedAttributeNames)) {
            $this->_whitelistedAttributeNames[] = $attributeCode;
        }
    }

    /**
     * Initialize blocks names
     */
    protected function _initBlocks()
    {
        $this->_stateBlockName              = 'catalog/layer_state';
        $this->_categoryBlockName           = 'trs/layer_filter_productType';
        $this->_attributeFilterBlockName    = 'catalog/layer_filter_attribute';
        $this->_priceFilterBlockName        = 'catalog/layer_filter_price';
        $this->_decimalFilterBlockName      = 'catalog/layer_filter_decimal';
        $this->_buyersguideFilterBlockName  = 'cartographee/buyersguide_layer_filter_car';
    }

    /**
     * Get attribute filter block name
     *
     * @deprecated after 1.4.1.0
     *
     * @return string
     */
    protected function _getAttributeFilterBlockName()
    {
        return 'catalog/layer_filter_attribute';
    }

    /**
     * Prepare child blocks
     *
     * @return Mage_Catalog_Block_Layer_View
     */
    protected function _prepareLayout()
    {
        $stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)
            ->setLayer($this->getLayer());

        $categoryBlock = $this->getLayout()->createBlock($this->_categoryBlockName)
            ->setLayer($this->getLayer())
            ->init();

        $buyersGuideBlock = $this->getLayout()->createBlock($this->_buyersguideFilterBlockName)
            ->setLayer($this->getLayer())
            ->init();

        $this->setChild('layer_state', $stateBlock);
        $this->setChild('category_filter', $categoryBlock);
        $this->setChild('buyers_guide', $buyersGuideBlock);

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {


            if ($attribute->getAttributeCode() == 'price') {
                $filterBlockName = $this->_priceFilterBlockName;
            } elseif ($attribute->getBackendType() == 'decimal') {
                $filterBlockName = $this->_decimalFilterBlockName;
            } else {
                $filterBlockName = $this->_attributeFilterBlockName;
            }

            $this->setChild($attribute->getAttributeCode() . '_filter',
                $this->getLayout()->createBlock($filterBlockName)
                    ->setLayer($this->getLayer())
                    ->setAttributeModel($attribute)
                    ->init());
        }

        $this->getLayer()->apply();

        return parent::_prepareLayout();
    }

    /**
     * Get layer object
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        return Mage::getSingleton('catalog/layer');
    }

    /**
     * Get all fiterable attributes of current category
     *
     * @return array
     */
    protected function _getFilterableAttributes()
    {
        $attributes = $this->getData('_filterable_attributes');
        if (is_null($attributes)) {
            $attributes = $this->getLayer()->getFilterableAttributes();
            $this->setData('_filterable_attributes', $attributes);
        }

        return $attributes;
    }

    /**
     * Get layered navigation state html
     *
     * @return string
     */
    public function getStateHtml()
    {
        return $this->getChildHtml('layer_state');
    }

    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array();
        if ($categoryFilter = $this->_getCategoryFilter()) {
            $filters[] = $categoryFilter;
        }

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            if (!$this->_isAttrWhitelisted($attribute->getAttributeCode())) {
                continue;
            }
            if($this->_isAttrExcluded($attribute->getAttributeCode())) {
                continue;
            }
            $filters[] = $this->getChild($attribute->getAttributeCode() . '_filter');
        }

        return $filters;
    }

    protected function _isAttrWhitelisted($attributeCode) {
        if (empty($this->_whitelistedAttributeNames)) {
            return true;
        }
        return in_array($attributeCode, $this->_whitelistedAttributeNames);
    }

    protected function _isAttrExcluded($attributeCode) {
        return in_array($attributeCode, $this->_excludedAttributeNames);
    }

    /**
     * Get category filter block
     *
     * @return Mage_Catalog_Block_Layer_Filter_Category
     */
    protected function _getCategoryFilter()
    {
        return $this->getChild('category_filter');
    }

    /**
     * Check availability display layer options
     *
     * @return bool
     */
    public function canShowOptions()
    {
        foreach ($this->getFilters() as $filter) {
            if ($filter->getItemsCount()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {
        return $this->canShowOptions() || count($this->getLayer()->getState()->getFilters());
    }

    /**
     * Retrieve Price Filter block
     *
     * @return Mage_Catalog_Block_Layer_Filter_Price
     */
    protected function _getPriceFilter()
    {
        return $this->getChild('_price_filter');
    }

    /**
     * Get url for 'Clear All' link
     *
     * @return string
     */
    public function getClearUrl()
    {
        return $this->getChild('layer_state')->getClearUrl();
    }
}


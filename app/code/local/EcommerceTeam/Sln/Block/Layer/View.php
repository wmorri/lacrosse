<?php
    /**
    * EcommerceTeam.com
    *
    * Seo Layered Navigation
    *
    * @category     Magento Extension
    * @copyright    Copyright (c) 2011 Ecommerce Team (http://www.ecommerce-team.com)
    * @author       Ecommerce Team
    * @version      3.0
    */


class EcommerceTeam_Sln_Block_Layer_View extends Mage_Catalog_Block_Layer_View{

    protected static $_filters;
    protected $_navigationGroup = 'default';
    protected $_stateBlock;

    const NAVIGATION_GROUP_DEFAULT  = 'default';
    const NAVIGATION_GROUP_TOP      = 'top';
    const NAVIGATION_GROUP_RIGHT    = 'right';

    public function __construct(){
        parent::__construct();
        $this->setIsEnabled(
            Mage::getStoreConfigFlag(
                sprintf('catalog/layered_navigation/%s_block_enabled', $this->_navigationGroup)));
        if (!Mage::registry('current_layer')) {
            Mage::register('current_layer', $this->getLayer());
        }
    }
    protected function _construct(){
        parent::_construct();
        $this->setNavigationGroup(self::NAVIGATION_GROUP_DEFAULT);
    }

    public function getStateBlock()
    {
        return $this->_stateBlock;
    }

    /**
     * @param string $groupName
     * @return void
     */
    public function setNavigationGroup($groupName)
    {
        $this->_navigationGroup = $groupName;
    }

    /**
     * Prepare child blocks
     *
     * @return Mage_Catalog_Block_Layer_View
     */
    protected function _prepareLayout()
    {

        /** @var $helper EcommerceTeam_Sln_Helper_Data */
        $helper = Mage::helper('ecommerceteam_sln');

        if (!$this->getIsEnabled()) {
            return $this;
        }

        if (!Mage::registry('layered_navigation_main_block')) {
            /** @var $layer EcommerceTeam_Sln_Model_Layer */
            $layer = $this->getLayer();
            $layer->initFilters();

            /** @var $headBlock Mage_Page_Block_Html_Head */
            $headBlock = $this->getLayout()->getBlock('head');

            if ($layer->getIsSliderEnabled()) {
                $headBlock->addJs('ecommerceteam/seo-layered-navigation/Utilities.js');
                $headBlock->addJs('ecommerceteam/seo-layered-navigation/Slider.js');
                $headBlock->addCss('css/ecommerceteam/sln/Slider.css');
            }


            if(Mage::helper('ecommerceteam_sln')->getConfigFlag('enable_ajax')){
                $headBlock->addJs('ecommerceteam/seo-layered-navigation.js');
                $headBlock->addJs('ecommerceteam/native.history.js');
            }
            if(Mage::helper('ecommerceteam_sln')->getConfigFlag('hidehtml') && !$this->getRequest()->getParam('is_ajax', false)){
                $headBlock->addJs('ecommerceteam/base64.js');
            }


        }

        $filterableAttributes = $this->_getFilterableAttributes();

        $categoryFilterEnabled = $helper->getConfigData('cat_filter_position') == $this->_navigationGroup;

        $this->_stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)
            ->setFilterableAttributes($filterableAttributes)
            ->setCategoryFilterEnabled($categoryFilterEnabled)
            ->setLayer($this->getLayer());

        $this->setChild('layer_state', $this->_stateBlock);

        if ($categoryFilterEnabled) {
            $categoryBlock = $this->getLayout()->createBlock($this->_categoryBlockName)
                ->setLayer($this->getLayer())
                ->init();
            $this->setChild('category_filter', $categoryBlock);
        }


        foreach ($filterableAttributes as $attribute) {
            if ($attribute->getAttributeCode() == 'price') {
                $filterBlockName = $this->_priceFilterBlockName;
            }
            elseif ($attribute->getBackendType() == 'decimal') {
                $filterBlockName = $this->_decimalFilterBlockName;
            }
            else {
                $filterBlockName = $this->_attributeFilterBlockName;
            }

            $childBlock = $this->getLayout()->createBlock($filterBlockName)
                                  ->setLayer($this->getLayer())
                                  ->setAttributeModel($attribute)
                                  ->init();

            $this->setChild($attribute->getAttributeCode() . '_filter_' . $this->_navigationGroup, $childBlock);
        }

        if (!Mage::registry('layered_navigation_main_block')) {
            $this->getLayer()->apply();
            Mage::register('layered_navigation_main_block', $this);
        }
        return $this;
    }

    /**
     * Return main layered block
     *
     * @return EcommerceTeam_Sln_Block_Layer_View
     */
    public function getMainBlock()
    {
        if(Mage::registry('layered_navigation_main_block')){
            return Mage::registry('layered_navigation_main_block');
        }
        return $this;
    }

    /**
     * Render block
     *
     * @return string
     */
    public function renderView()
    {
        $html = trim(parent::renderView());
        if(Mage::helper('ecommerceteam_sln')->getConfigFlag('hidehtml') && !$this->getRequest()->getParam('is_ajax', false)){
            $html = '<script type="text/javascript">/* <!-- */ document.write(Base64.decode("'.base64_encode($html).'")); /* --> */</script>';
        }
        return $html;
    }
    /**
     * Initialize blocks names
     */
    protected function _initBlocks()
    {
        $this->_stateBlockName           = 'ecommerceteam_sln/layer_state';
        $this->_categoryBlockName        = 'ecommerceteam_sln/layer_filter_category';
        $this->_attributeFilterBlockName = 'ecommerceteam_sln/layer_filter_attribute';
        $this->_priceFilterBlockName     = 'ecommerceteam_sln/layer_filter_price';
        $this->_decimalFilterBlockName   = 'ecommerceteam_sln/layer_filter_decimal';
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute_Collection|mixed
     */
    protected function _getFilterableAttributes()
    {
        /** @var $attributeCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute_Collection */
        $attributeCollection = $this->getData('_filterable_attributes');
        if (is_null($attributeCollection)) {
            $attributeCollection = $this->getLayer()->getFilterableAttributes();
            if ($this->_navigationGroup == self::NAVIGATION_GROUP_DEFAULT) {
                foreach ($attributeCollection as $key=>$attribute) {
                    $navigationGroup = $attribute->getNavigationGroup();
                    if($navigationGroup && $this->_navigationGroup != $navigationGroup){
                        $attributeCollection->removeItemByKey($key);
                    }
                }
            } else {
                foreach ($attributeCollection as $key=>$attribute) {
                    $navigationGroup = $attribute->getNavigationGroup();
                    if($this->_navigationGroup != $navigationGroup){
                        $attributeCollection->removeItemByKey($key);
                    }
                }
            }
            $this->setData('_filterable_attributes', $attributeCollection);
        }

        return $attributeCollection;
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
            $filters[] = $this->getChild($attribute->getAttributeCode() . '_filter_' . $this->_navigationGroup);
        }
        return $filters;
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
        return $this->_attributeFilterBlockName;
    }

    /**
     * Retrieve Price Filter block
     *
     * @return Mage_Catalog_Block_Layer_Filter_Price
     */
    protected function _getPriceFilter()
    {
        return $this->getChild('_price_filter_' . $this->_navigationGroup);
    }

    /**
     *
     * Return current block id in html
     *
     * @return string
     */
    public function getBlockId()
    {
        if (!$this->getData('block_id')) {
            $this->setData('block_id', 'sln-filter-' . $this->_navigationGroup);
        }
        return $this->getData('block_id');
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {

        if ($this->getIsEnabled()) {
            if($this->canShowOptions() || count($this->getStateBlock()->getFilters())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check availability display layer options
     *
     * @return bool
     */
    public function canShowOptions()
    {
        foreach ($this->getFilters() as $filter) {
            if ($filter->getAllItemsCount()) {
                return true;
            }
        }
        return false;
    }
}

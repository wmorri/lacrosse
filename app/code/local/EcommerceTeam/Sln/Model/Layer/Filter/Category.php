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


class EcommerceTeam_Sln_Model_Layer_Filter_Category
    extends Mage_Catalog_Model_Layer_Filter_Category
{

    protected $_allItems;


    /**
     * Apply category filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mage_Core_Block_Abstract $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $_request = Mage::getSingleton('ecommerceteam_sln/request');
        $filters  = $_request->getValue('cat');

        if (empty($filters)) {
            return $this;
        }

        $value = array();

        foreach ($filters as $filter) {
            $category = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($filter);

            if ($this->_isValidCategory($category)) {
                $value[] = $filter;


                $this->getLayer()->getState()->addFilter(
                    $this->_createItem($category->getName(), $filter)
                );
            }
        }

        if (!empty($value)) {
            $this->_getResource()->applyFilterToCollection($this, $value);
        }

        return $this;
    }

    /**
     * Initialize filter items
     *
     * @return  Mage_Catalog_Model_Layer_Filter_Abstract
     */
    protected function _initItems()
    {
        $data     = $this->_getItemsData(true);
        $allItems = array();
        $items    = array();

        foreach ($data as $itemData) {
            $item = $this->_createItem(
               $itemData['label'],
                $itemData['value'],
                $itemData['count'],
                $itemData['is_selected']
            );
            $allItems[] = $item;
            if (!$item->getIsSelected()){
                $items[] = $item;
            }
        }

        $this->_items    = $items;
        $this->_allItems = $allItems;
        return $this;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {

        if (!Mage::helper('ecommerceteam_sln')->getConfigFlag('enable_category')) {
            return array();
        }

        $key  = $this->getLayer()->getStateKey().'_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            /** @var $currentCategory Mage_Catalog_Model_Category */
            $currentCategory = $this->getLayer()->getCurrentCategory();
            $categories      = $currentCategory->getChildrenCategories();
            $request         = Mage::getSingleton('ecommerceteam_sln/request');
            $selected        = array();

            if ($value = $request->getValue($this->_requestVar)) {
                $selected = array_merge($selected, $value);
            }

            $data = array();
            if (count($categories) > 0) {
                $categoryCount = $this->_getResource()->getCount($this, $categories);
                foreach ($categories as $category) {
                    if ($category->getIsActive()
                        && isset($categoryCount[$category->getId()])
                        && $categoryCount[$category->getId()] > 0 || in_array($category->getId(), $selected)) {
                        $value = $category->getId();
                        $data[] = array(
                            'label' => Mage::helper('core')->htmlEscape($category->getName()),
                            'value' => $value,
                            'count' => isset($categoryCount[$category->getId()]) ? $categoryCount[$category->getId()] : 0,
                            'is_selected' => in_array($category->getId(), $selected),
                        );
                    }
                }
            }
            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    /**
     * Retrieve resource instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Category
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('catalog/layer_filter_category');
        }
        return $this->_resource;
    }

    /**
     * Get all fiter items count
     *
     * @return int
     */
    public function getAllItems()
    {
        if (is_null($this->_allItems)) {
            $this->_initItems();
        }
        return $this->_allItems;
    }

    /**
     * Get all fiter items count
     *
     * @return int
     */
    public function getAllItemsCount()
    {
        return count($this->getAllItems());
    }

    /**
     * Get frontend type for filter
     *
     * @return null|string
     */
    public function getFrontendType()
    {
        return Mage::helper('ecommerceteam_sln')->getConfigData('cat_filter_type');
    }
    /**
     * @param string $label
     * @param mixed $value
     * @param int $count
     * @param bool $isSelected
     * @return EcommerceTeam_Sln_Model_Layer_Filter_Item
     */
    protected function _createItem($label, $value, $count = 0, $isSelected = false)
    {
        return Mage::getModel('catalog/layer_filter_item')
            ->setFilter($this)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count)
            ->setIsSelected($isSelected);
    }
}

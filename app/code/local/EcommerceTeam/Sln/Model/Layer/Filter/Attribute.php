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


class EcommerceTeam_Sln_Model_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute {

    protected $_allItems;

    /**
     * Apply attribute option filter to product collection
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Varien_Object $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {

        $_request = Mage::getSingleton('ecommerceteam_sln/request');
        $values = $_request->getValue($this->_requestVar);

        if (empty($values)) {
            return $this;
        }

        $this->_getResource()->applyFilterToCollection($this, $values);

        foreach($values as $filter){

            $text = $this->_getOptionText($filter);

            $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));

        }


        return $this;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @param bool $showSelected
     * @return array|null
     */
    protected function _getItemsData($showSelected = false)
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $request = Mage::getSingleton('ecommerceteam_sln/request');

        $selected = array();

        if($value = $request->getValue($this->_requestVar)){

            $selected = array_merge($selected, $value);

        }


        $key = $this->getLayer()->getStateKey().'_'.$this->_requestVar.'_'.intval($showSelected);
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $options = $attribute->getFrontend()->getSelectOptions();
            $optionsCount = $this->_getResource()->getCount($this);
            $data = array();

            foreach ($options as $option) {
                if (is_array($option['value']) || !$showSelected && in_array($option['value'], $selected)) {
                    continue;
                }
                if (Mage::helper('core/string')->strlen($option['value'])) {
                    $value = $option['value'];
                    // Check filter type
                    if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS && !(in_array($option['value'], $selected))) {
                        if (!empty($optionsCount[$option['value']])) {
                            $data[] = array(
                                'label'     => $option['label'],
                                'value'     => $value,
                                'count'     => $optionsCount[$option['value']],
                                'is_selected'  => in_array($value, $selected),
                            );
                        }
                    }
                    else {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $value,
                            'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                            'is_selected'  => in_array($value, $selected),
                        );
                    }
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG.':'.$attribute->getId()
            );

            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    /**
     * Get filter value for reset current filter state
     *
     * @param null $value
     * @return null|string
     */
    public function getResetValue($value = null)
    {
        $request = Mage::getSingleton('ecommerceteam_sln/request');
        if ($value && ($current_value = $request->getValue($this->_requestVar))) {
            if (false !== ($position = array_search($value, $current_value))) {
                unset($current_value[$position]);
                if (!empty($current_value)) {
                    return implode(',', $current_value);
                }
            }
        }
        return null;
    }

    /**
     * Get all filter items
     *
     * @return array
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
        return $this->getAttributeModel()->getFrontendType();
    }

    public function getComment()
    {
        return $this->getAttributeModel()->getComment();
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

        $this->_items = $items;
        $this->_allItems = $allItems;
        return $this;
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

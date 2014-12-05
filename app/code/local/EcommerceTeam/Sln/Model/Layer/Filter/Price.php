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


class EcommerceTeam_Sln_Model_Layer_Filter_Price
    extends Mage_Catalog_Model_Layer_Filter_Price
{
    protected $_selectedValues;
    protected $_allItems;
    /**
     * Get price range for building filter steps
     *
     * @return int
     */
    public function getPriceRange()
    {

        if($range = intval(Mage::helper('ecommerceteam_sln')->getConfigData('pricerange'))){
            if($range > 0){
                return $range;
            }
        }

        return parent::getPriceRange();

    }

    /**
     * Get selected filters
     *
     * @return array|string
     */
    protected function getSelected(){

        if(is_null($this->_selectedValues)){
            /** @var $request EcommerceTeam_Sln_Model_Request */
            $request = Mage::getSingleton('ecommerceteam_sln/request');
            $this->_selectedValues = $request->getValue($this->_requestVar);
        }

        return $this->_selectedValues;

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
     * Get data for build price filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $key      = $this->_getCacheKey();
        $data     = $this->getLayer()->getAggregator()->getCacheData($key);
        $selected = $this->getSelected();
        if (!is_array($selected)) {
            $selected = array();
        }

        if ($data === null) {
            $range      = $this->getPriceRange();
            $dbRanges   = $this->getRangeItemCounts($range);
            $data       = array();

            foreach ($dbRanges as $index=>$count) {
                $value = sprintf('%d,%d', $index, $range);
                /*if (!empty($selected_values)) {
                    if (in_array($value, $selected_values)) {
                        continue;
                    }
                }*/
                $data[] = array(
                    'label' => $this->_renderItemLabel($range, $index),
                    'value' => $value,
                    'count' => $count,
                    'is_selected' => in_array($value, $selected),
                );
            }

            $tags = array(
                Mage_Catalog_Model_Product_Type_Price::CACHE_TAG,
            );
            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }

        return $data;
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Core_Block_Abstract $filterBlock
     * @return EcommerceTeam_Sln_Model_Layer_Filter_Price
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        /**
         * Request value must be string: $index,$range
         */

        $_request = Mage::getSingleton('ecommerceteam_sln/request');
        $requestValue = $_request->getValue($this->getRequestVar());

        if (empty($requestValue)) {
            return $this;
        }

        if (EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_INPUT == $this->getAttributeModel()->getFrontendType()
            || EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_SLIDER == $this->getAttributeModel()->getFrontendType()) {
            if (!empty($requestValue)) {
                /** @var $resourceModel EcommerceTeam_Sln_Model_Resource_Eav_Mysql4_Layer_Filter_Price */
                $resourceModel = $this->_getResource();
                $resourceModel->applyFilterToCollection($this, $requestValue, true);
                $stateBlock   = $this->getLayer()->getState();
                $stateBlock->addFilter($this->_createItem(Mage::helper('catalog')->__('%s - %s', $requestValue['start'], $requestValue['end']), implode(',', $requestValue)));
            }
        } else {
            $value = array();
            foreach ($requestValue as $_value) {
                $_value = explode(',', $_value);
                if (isset($_value[0]) && $_value[1]) {
                    $value[] = array(
                        'index'=>$_value[0],
                        'range'=>$_value[1],
                    );
                }
            }

            if(!empty($value)){
                $this->setPriceRange((int)$value[0]['range']);
                /** @var $resourceModel EcommerceTeam_Sln_Model_Resource_Eav_Mysql4_Layer_Filter_Price */
                $resourceModel = $this->_getResource();
                $resourceModel->applyFilterToCollection($this, $value);
                $stateBlock   = $this->getLayer()->getState();
                foreach($value as $_value){
                    $stateBlock->addFilter($this->_createItem($this->_renderItemLabel($_value['range'], $_value['index']), $_value));
                }
            }
        }
        return $this;
    }


    /**
     * @param null $value
     * @return null|string
     */
    public function getResetValue($value = null)
    {
        $request = Mage::getSingleton('ecommerceteam_sln/request');
        if($value && $request->getValue($this->_requestVar, false)){
            if(isset($value['index']) && isset($value['range'])){
                $value = sprintf('%d,%d', $value['index'], $value['range']);
                $selectedValues = $this->getSelected();
                if(false !== ($position = array_search($value, $selectedValues))){
                    unset($selectedValues[$position]);
                    if(!empty($selectedValues)){
                        return implode(',', $selectedValues);
                    }
                }
            }
        }
        return null;
    }

    /**
     * Get minimal price from layer products set
     *
     * @return float
     */
    public function getMinPriceInt()
    {
        $minPrice = $this->getData('min_price_int');
        if (is_null($minPrice)) {
            $minPrice = $this->_getResource()->getMinPrice($this);
            $minPrice = floor($minPrice);
            $this->setData('min_price_int', $minPrice);
        }
        return $minPrice;
    }
	
	 /**
     * Get maximum price from layer products set
     *
     * @return float
     */
    public function getMaxPriceInt()
    {
        $maxPrice = $this->getData('max_price_int');
        if (is_null($maxPrice)) {
            $maxPrice = $this->_getResource()->getMaxPrice($this);
            $maxPrice = floor($maxPrice);
            $this->setData('max_price_int', $maxPrice);
        }

        return $maxPrice;
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
        return $this->getAttributeModel()->getFrontendType();
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

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


class EcommerceTeam_Sln_Model_Layer_Filter_Decimal
    extends Mage_Catalog_Model_Layer_Filter_Decimal
{
    protected $_selectedValues;
    protected $_allItems;

    /**
     * @return array
     */
    protected function getSelected()
    {
        if(!is_array($this->_selectedValues)){
            $request = Mage::getSingleton('ecommerceteam_sln/request');
            $this->_selectedValues = $request->getValue($this->getRequestVar());
        }
        return $this->_selectedValues;
    }

    /**
     * Apply decimal range filter to product collection
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Catalog_Block_Layer_Filter_Decimal $filterBlock
     * @return Mage_Catalog_Model_Layer_Filter_Decimal
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
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

            if (!empty($value)) {
                $range = (int)$value[0]['range'];

                $this->setRange($range);

                /** @var $resource EcommerceTeam_Sln_Model_Resource_Eav_Mysql4_Layer_Filter_Decimal */
                $resource = $this->_getResource();
                $resource->applyFilterToCollection($this, $value);

                $state_block = $this->getLayer()->getState();

                foreach ($value as $_value) {
                    $state_block->addFilter($this->_createItem($this->_renderItemLabel($_value['range'], $_value['index']), $_value));
                }
            }
        }
        return $this;
    }

    /**
     * Retrieve range for building filter steps
     *
     * @return int
     */
    public function getRange()
    {
        if ($range = intval(Mage::helper('ecommerceteam_sln')->getConfigData('pricerange'))) {
            if ($range > 0) {
                return $range;
            }
        }
        return parent::getRange();
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
     * Retrieve data for build decimal filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $data     = null;
        $selected = $this->getSelected();
        if(!is_array($selected)){
            $selected = array();
        }
        if ($data === null) {
            $data       = array();
            $range      = $this->getRange();
            $dbRanges   = $this->getRangeItemCounts($range);

            foreach ($dbRanges as $index => $count) {
                $value = sprintf('%d,%d', $index, $range);
                /*if(!empty($selected)){
                    if(in_array($value, $selected)){
                        continue;
                    }
                }*/
                $data[] = array(
                    'label' => $this->_renderItemLabel($range, $index),
                    'value' => $index . ',' . $range,
                    'count' => $count,
                    'is_selected' => in_array($value, $selected),
                );
            }
        }
        return $data;
    }

    /**
     * Get value for reset
     *
     * @param null $value
     * @return null|string
     */
    public function getResetValue($value = null)
    {
        $request = Mage::getSingleton('ecommerceteam_sln/request');
        if ($value && $request->getValue($this->getRequestVar(), false)) {
            if (isset($value['index']) && isset($value['range'])) {
                $value           = sprintf('%d,%d', $value['index'], $value['range']);
                $selectedValues = $this->getSelected();
                if (false !== ($position = array_search($value, $selectedValues))) {
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

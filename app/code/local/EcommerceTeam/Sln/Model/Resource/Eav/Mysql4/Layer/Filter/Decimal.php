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


class EcommerceTeam_Sln_Model_Resource_Eav_Mysql4_Layer_Filter_Decimal
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize connection and define main table name
     *
     */
    protected function _construct()
    {
        $this->_init('catalog/product_index_eav_decimal', 'entity_id');
    }

    public function prepareSelect($filter, $value, $select, $rangeMode = false){

        $attribute  = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", Mage::app()->getStore()->getId())
        );

        $select->join(
            array($tableAlias => $this->getMainTable()),
            join(' AND ', $conditions),
            array()
        );

        $priceExpr = "{$tableAlias}.value";
        $where = array();

        if ($rangeMode) {
            $start = $value['start'];
            $end   = $value['end'];

            $select->where(sprintf($priceExpr . ' >= %s', $start) . ( $end > 0 ? ' AND ' . sprintf($priceExpr . ' <= %d', $end) : ''));

        } else {
            foreach((array)$value as $_value){
                $where[] = sprintf($priceExpr . ' >= %s', ($_value['range'] * ($_value['index'] - 1))) . ' AND ' . sprintf($priceExpr . ' < %d', ($_value['range'] * $_value['index']));
            }
            $select->where(implode(' OR ', $where));
        }
/*
        foreach ((array)$value as $_value) {
            $where[] = sprintf($priceExpr . ' >= %s', ($_value['range'] * ($_value['index'] - 1))) . ' AND ' . sprintf($priceExpr . ' < %d', ($_value['range'] * $_value['index']));
        }

        $select->where(implode(' OR ', $where));*/
    }

    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Decimal $filter
     * @param int|string $value
     * @param $rangeMode bool
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Decimal
     */
    public function applyFilterToCollection($filter, $value, $rangeMode = false)
    {
        /** @var $layer EcommerceTeam_Sln_Model_Layer */
        $layer               = $filter->getLayer();
        /** @var $attributeCollection Mage_Eav_Model_Mysql4_Entity_Attribute_Collection */
        $attributeCollection = $layer->getFilterableAttributes();
        $select              = $layer->getProductCollection()->getSelect();

        $this->prepareSelect($filter, $value, $select, $rangeMode);

        if (!empty($attributeCollection)) {
            $attributeCode = $filter->getRequestVar();
            $baseSelect    = $layer->getSelectWithoutFilter();

            foreach ($baseSelect as $code => $select) {
                $attribute = $attributeCollection->getItemByColumnValue('attribute_code', $code);
                if ($attribute) {
                    if (EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_SLIDER == $attribute->getFrontendType()) {
                        continue;
                    }
                }
                if ($attributeCode != $code) {
                    $this->prepareSelect($filter, $value, $select, $rangeMode);
                }
            }
        }
        return $this;
    }

    /**
     * Retrieve array of minimal and maximal values
     *
     * @param Mage_Catalog_Model_Layer_Filter_Decimal $filter
     * @return array
     */
    public function getMinMax($filter)
    {
        $select     = $this->_getSelect($filter);
        $adapter    = $this->_getReadAdapter();

        $select->columns(array(
            'min_value' => new Zend_Db_Expr('MIN(decimal_index.value)'),
            'max_value' => new Zend_Db_Expr('MAX(decimal_index.value)'),
        ));

        $result     = $adapter->fetchRow($select);

        return array($result['min_value'], $result['max_value']);
    }

    /**
     * Retrieve clean select with joined index table
     *
     * Joined table has index
     *
     * @param Mage_Catalog_Model_Layer_Filter_Decimal $filter
     * @return Varien_Db_Select
     */
    protected function _getSelect($filter)
    {
        $_select = $filter->getLayer()->getSelectWithoutFilter($filter->getRequestVar());

        if ($_select) {
            $select = clone $_select;
        } else {
            $collection = $filter->getLayer()->getProductCollection();
            $select = clone $collection->getSelect();
        }

        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::GROUP);

        $attributeId = $filter->getAttributeModel()->getId();
        $storeId     = Mage::app()->getStore()->getId();
        $select->join(
            array('decimal_index' => $this->getMainTable()),
            "e.entity_id=decimal_index.entity_id AND decimal_index.attribute_id={$attributeId}"
                . " AND decimal_index.store_id={$storeId}",
            array()
        );

        return $select;
    }

    /**
     * Retrieve array with products counts per range
     *
     * @param Mage_Catalog_Model_Layer_Filter_Decimal $filter
     * @param int $range
     * @return array
     */
    public function getCount($filter, $range)
    {
        $select     = $this->_getSelect($filter);
        $adapter    = $this->_getReadAdapter();

        $countExpr  = new Zend_Db_Expr("COUNT(*)");
        $rangeExpr  = new Zend_Db_Expr("FLOOR(decimal_index.value / {$range}) + 1");

        $select->columns(array(
            'range' => $rangeExpr,
            'count' => $countExpr
        ));
        $select->group('range');

        return $adapter->fetchPairs($select);
    }
}

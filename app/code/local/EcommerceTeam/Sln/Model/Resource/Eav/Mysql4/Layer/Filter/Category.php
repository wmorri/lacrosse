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


class EcommerceTeam_Sln_Model_Resource_Eav_Mysql4_Layer_Filter_Category
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
{

    /**
     * @param Varien_Object $filter
     * @param array|string|int $value
     * @param Varien_Db_Select $select
     * @return void
     */
    public function prepareSelect($filter, $value, $select)
    {
        $alias = 'category_idx';
        $value = (array)$value;

        foreach ($value as $_value) {
            $where[] = intval($_value);
        }

        $select->join(
                array($alias => Mage::getSingleton('core/resource')->getTableName('catalog/category_product_index')),
                $alias.'.category_id IN ('.implode(',',$where).') AND '.$alias.'.product_id = e.entity_id',
                array()
            );

    }

     /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */


    public function applyFilterToCollection($filter, $value)
    {

        $collection = $filter->getLayer()->getProductCollection();

        $this->prepareSelect($filter, $value, $collection->getSelect());

        $base_select = $filter->getLayer()->getSelectWithoutFilter();

        foreach($base_select as $code=>$select){


        if('cat' != $code){

        $this->prepareSelect($filter, $value, $select);

        }

        }

        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
        /** @var $layer EcommerceTeam_Sln_Model_Layer */
        $layer      = $filter->getLayer();
        $categories = $layer->getCurrentCategory()->getChildrenCategories();
        $connection = $this->_getReadAdapter();
        $select     = $layer->getSelectWithoutFilter('cat');

        if (!$select) {
            $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        }
        $where  = array();
        $catIds = array();

        foreach($categories as $category){
            $catIds[] = $category->getEntityId();
        }

        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::GROUP);



        $select->join(
            array('child_cat_index' => Mage::getSingleton('core/resource')->getTableName('catalog/category_product_index')),
            'child_cat_index.category_id IN ('.implode(',',$catIds).') AND child_cat_index.product_id = e.entity_id',
            array('value'=>'category_id')
        );

        $fields = array('count'=>'COUNT(DISTINCT child_cat_index.product_id)');

        $select->columns($fields);
        $select->group('child_cat_index.category_id');

        return $connection->fetchPairs($select);

    }
}

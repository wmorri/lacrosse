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

/**
 * @method EcommerceTeam_Sln_Model_Layer setIsSliderEnabled(boolean $value)
 * @method boolean getIsSliderEnabled()
 */

class EcommerceTeam_Sln_Model_Layer extends Mage_Catalog_Model_Layer
{
    protected $select_without_filter = array();

    public function __construct()
    {
        if (!Mage::registry('layer_loaded')) {
            Mage::register('layer_loaded', true);
        }
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getProductCollection()
    {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = $this->getCurrentCategory()->getProductCollection();
            $collection->groupByAttribute('entity_id');
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }
        return $collection;
    }

    /**
     * @param string|null $request_var
     * @return bool|Varien_Db_Select
     */
    public function getSelectWithoutFilter($request_var = null)
    {
        if ($request_var) {
            if (isset($this->select_without_filter[$request_var])) {
                return $this->select_without_filter[$request_var];
            } else {
                return false;
            }
        } else {
            return $this->select_without_filter;
        }
    }

    /**
     * Initialize request model
     *
     * @return void
     */
    public function initRequest()
    {
        Mage::getSingleton('ecommerceteam_sln/request');
    }

    /**
     * Initialize filter configuration
     *
     * @return void
     */
    public function initFilters()
    {
        $this->initRequest();
        $filterableAttributes    = $this->getFilterableAttributes();
        $product_collection        = $this->getProductCollection();
        /** @var $request EcommerceTeam_Sln_Model_Request */
        $request = Mage::getSingleton('ecommerceteam_sln/request');
        if ($request->getValue('price')) {
            $this->select_without_filter['price'] = clone $product_collection->getSelect();
        }
        if ($request->getValue('cat')) {
            $this->select_without_filter['cat'] = clone $product_collection->getSelect();
        }
        foreach ($filterableAttributes as $attribute) {
            $attribute_code = $attribute->getAttributeCode();
            if($request->getValue($attribute_code, false)){
                $this->select_without_filter[$attribute_code] = clone $product_collection->getSelect();
            }
            if (EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_SLIDER == $attribute->getFrontendType()) {
                $this->setIsSliderEnabled(true);
            }
        }
    }

    /**
     * Add advanced data to collection
     *
     * @param  $collection
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute_Collection
     */
    protected function _prepareAttributeCollection($collection)
    {
        $dataTable = 'ecommerceteam_sln_data';
        $collection = parent::_prepareAttributeCollection($collection);
        $collection->addIsFilterableFilter();
        $collection->getSelect()->joinLeft(
            array($dataTable => Mage::getSingleton('core/resource')->getTableName('ecommerceteam_sln_attribute_data')),
            "`main_table`.`attribute_id` = `{$dataTable}`.`attribute_id`",
            array(
                'frontend_type'     => 'frontend_type',
                'navigation_group'  => 'group_id',
                'comment'           => 'comment',
            )
        );
        return $collection;
    }
}

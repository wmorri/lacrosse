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


class EcommerceTeam_Sln_Block_Layer_State extends Mage_Catalog_Block_Layer_State
{

    /**
     * Initialize Layer State template
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('ecommerceteam/sln/layer/state.phtml');
    }

    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        if($this->getRequest()->getModuleName() == 'catalogsearch'){
            $params['_query']       = array('q'=>strval($this->getRequest()->getParam('q')));
            $params['_escape']      = true;
        }else{
            $params['_current']     = true;
            $params['_use_rewrite'] = true;
            $params['_query']       = array();
            $params['_escape']      = true;
            $params['_disable_filter']    = true;
        }

        //if(Mage::helper('ecommerceteam_sln')->getConfigFlag('enable_ajax')){
            $params['_query']['reset_filter'] = 1;
        //}
        return Mage::getUrl('*/*/*', $params);
    }

    public function getActiveFilters()
    {
        $filters    = array();
        $allFilters = parent::getActiveFilters();
        /** @var $helper EcommerceTeam_Sln_Helper_Data */
        $helper     = Mage::helper('ecommerceteam_sln');
        if (!empty($allFilters)) {
            /** @var $attributes Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */
            $attributes            = $this->getFilterableAttributes();
            $categoryFilterEnabled = $this->getCategoryFilterEnabled();
            foreach ($allFilters as $item) {
                if ($attributeModel = $item->getFilter()->getData('attribute_model')) {
                    if (!empty($attributes) && $attributes->getItemByColumnValue('attribute_code', $attributeModel->getAttributeCode())) {
                        $filters[] = $item;
                    }
                } else {
                    if ('cat' == $item->getFilter()->getRequestVar() && $categoryFilterEnabled) {
                        $filters[] = $item;
                    }
                }
            }
        }
        return $filters;
    }
}

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

class EcommerceTeam_Sln_Block_Layer_View_Search extends EcommerceTeam_Sln_Block_Layer_View
{
    /**
    * Get attribute filter block name
    *
    * @deprecated after 1.4.1.0
    *
    * @return string
    */
    public function getLayer()
    {
        return Mage::getSingleton('catalogsearch/layer');
    }


    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {

        $availableResCount = (int) Mage::app()->getStore()
            ->getConfig(Mage_CatalogSearch_Model_Layer::XML_PATH_DISPLAY_LAYER_COUNT );

        if (!$availableResCount
            || ($availableResCount>=$this->getLayer()->getProductCollection()->getSize())) {
            return parent::canShowBlock();
        }
        return false;
    }

}

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


class EcommerceTeam_Sln_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{

    public function getPagerUrl($params=array())
    {
        $params['is_ajax'] = null;
        return parent::getPagerUrl($params);
    }
}

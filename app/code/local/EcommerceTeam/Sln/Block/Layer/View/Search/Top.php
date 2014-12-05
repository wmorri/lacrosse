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


class EcommerceTeam_Sln_Block_Layer_View_Search_Top extends EcommerceTeam_Sln_Block_Layer_View_Search
{
   public function _construct(){
        parent::_construct();
        $this->setNavigationGroup(self::NAVIGATION_GROUP_TOP);
    }
}

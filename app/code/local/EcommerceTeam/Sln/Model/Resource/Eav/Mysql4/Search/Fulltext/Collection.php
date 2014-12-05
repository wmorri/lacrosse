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

class EcommerceTeam_Sln_Model_Resource_Eav_Mysql4_Search_Fulltext_Collection
    extends Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection
{
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->reset(Zend_Db_Select::GROUP);
        return $select;
    }
}

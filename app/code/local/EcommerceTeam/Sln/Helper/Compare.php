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

class EcommerceTeam_Sln_Helper_Compare extends Mage_Catalog_Helper_Product_Compare
{
    public function getEncodedUrl($url = null)
    {

        if (!$url) {
            $url = $this->getCurrentUrl();
        }
        return $this->urlEncode(preg_replace('/is_ajax=\d/i', '', $url));
    }

}

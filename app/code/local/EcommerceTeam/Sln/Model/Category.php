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

class EcommerceTeam_Sln_Model_Category extends Mage_Catalog_Model_Category
{
    /**
     * Get category url
     *
     * @return string
     */
    public function getUrl()
    {
        $url = $this->_getData('url');

        if (is_null($url)) {
            if(!is_null($this->getRequestPath())){
                $path = $this->getRequestPath();
            }elseif(!is_null($this->getUrlPath())){
                $path = $this->getUrlPath();
            }else{
                $path = $this->getUrlKey();
            }

            $url = $this->getUrlInstance()->getUrl().$path;
            $this->setData('url', $url);
        }
        return $url;
    }
}

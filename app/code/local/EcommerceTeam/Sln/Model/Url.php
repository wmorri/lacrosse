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

class EcommerceTeam_Sln_Model_Url extends Mage_Core_Model_Url
{
    /**
     * @param array $routeParams
     * @return mixed|null|string
     */
    public function getRoutePath($routeParams = array())
    {
        if (!$this->hasData('route_path')) {
            $routePath = $this->getRequest()->getAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS);
            if (!empty($routeParams['_use_rewrite']) && ($routePath !== null)) {
                /** @var $request EcommerceTeam_Sln_Model_Request */
                $request = Mage::getSingleton('ecommerceteam_sln/request');
                $filterRouteParams = $request->getOriginalValue();
                $params            = array();

                foreach ($filterRouteParams as $key=>$value) {
                    $params[] = $key;
                    $params[] = implode(',', (array)$value);
                }

                if (!empty($params) && empty($routeParams['_disable_filter'])) {
                    /** @var $helper EcommerceTeam_Sln_Helper_Data */
                    $helper       = Mage::helper('ecommerceteam_sln');
                    $urlSeparator = $helper->getConfigData('url_separator');
                    $routePath = trim($routePath, '/') . '/'. $urlSeparator . '/'. implode('/', $params).'/';
                }

                $this->setData('route_path', $routePath);
                return $routePath;
            }
            $routePath = $this->getActionPath();
            if ($this->getRouteParams()) {
                foreach ($this->getRouteParams() as $key=>$value) {
                    if (is_null($value) || false===$value || ''===$value || !is_scalar($value)) {
                        continue;
                    }
                    $routePath .= $key.'/'.$value.'/';
                }
            }
            if ($routePath != '' && substr($routePath, -1, 1) !== '/') {
                $routePath.= '/';
            }
            $this->setData('route_path', $routePath);
        }
        return $this->_getData('route_path');
    }
}

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


class EcommerceTeam_Sln_Model_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item {

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        /** @var $helper EcommerceTeam_Sln_Helper_Data */
        $helper     = Mage::helper('ecommerceteam_sln');
        /** @var $request EcommerceTeam_Sln_Model_Request */
        $request    = Mage::getSingleton('ecommerceteam_sln/request');
        $requestVar = $this->getFilter()->getRequestVar();
        $filterParams = array();
        if ($values = $request->getValue()) {
            ksort($values);
            foreach ($values as $_requestVar => $requestValue) {
                if ($_requestVar == $requestVar) {
                    $value = $this->getValue();
                    if (is_array($value)) {
                        $value = sprintf('%d,%d', $value['index'], $value['range']);
                    }
                    if (false !== ($key = array_search($value, $requestValue))) {
                        unset($requestValue[$key]);
                    }
                }
                if (!empty($requestValue)) {
                    foreach ($requestValue as $key=>$value) {
                        if ($_value = $request->getFilterKeyById($_requestVar, $value)) {
                            $requestValue[$key] = $_value;
                        }
                    }
                    if (!isset($requestValue['start']) && !isset($requestValue['end'])) {
                        sort($requestValue);
                    }
                    $filterParams[] = $_requestVar;
                    $values[$_requestVar] = $filterParams[] = implode(',', $requestValue);
                }
            }
        }

        $params['_use_rewrite']     = true;
        $params['_escape']          = true;
        $params['_nosid']            = true;

        if (Mage::app()->getFrontController()->getRequest()->getModuleName() == 'catalogsearch') {
            $params['_query'] = array('q'=>Mage::app()->getFrontController()->getRequest()->getParam('q'));
        } else {
            $params['_disable_filter']    = true;
        }
        if (empty($filterParams)) {
            $params['_query']['reset_filter'] = 1;
            return Mage::getUrl('*/*/*', $params);
        }

        if (Mage::app()->getFrontController()->getRequest()->getModuleName() == 'catalogsearch') {
            $params = array_merge($params, $values);
            return Mage::getUrl('*/*/*', $params);
        } else {
            $params['_disable_filter']    = true;
            $baseUrl      = Mage::getUrl('*/*/*', $params);
            $urlSeparator = $helper->getConfigData('url_separator');
            return sprintf('%s/%s/%s', trim($baseUrl, '/'), $urlSeparator, implode('/', $filterParams));
        }
    }

    /**
     * Get filter item url
     *
     * @param bool $singleMode
     * @return string
     */
    public function getUrl($singleMode = false)
    {
        /** @var $helper EcommerceTeam_Sln_Helper_Data */
        $helper     = Mage::helper('ecommerceteam_sln');
        /** @var $request EcommerceTeam_Sln_Model_Request */
        $request = Mage::getSingleton('ecommerceteam_sln/request');
        $requestVar = $this->getFilter()->getRequestVar();
        $filterParams = array();
        $values = $request->getValue();

        if(empty($values)){
            $values = array();
        }

        if(!$singleMode && isset($values[$requestVar])){
            $values[$requestVar][] = $this->getValue();

        }else{
            $values[$requestVar] = array($this->getValue());
        }
        ksort($values);

        foreach($values as $requestVar=>$requestValue){
            foreach($requestValue as $key=>$value){
                if($_value = $request->getFilterKeyById($requestVar, $value)){
                    $requestValue[$key] = $_value;
                }
            }
            if (!isset($requestValue['start']) && !isset($requestValue['end'])) {
                sort($requestValue);
            }
            $filterParams[] = $requestVar;
            $values[$requestVar] = $filterParams[] = implode(',', $requestValue);
        }

        $params['_use_rewrite']     = true;
        $params['_escape']          = true;
        $params['_nosid']            = true;

        if (Mage::app()->getFrontController()->getRequest()->getModuleName() == 'catalogsearch') {
            $params['_query'] = array('q'=>Mage::app()->getFrontController()->getRequest()->getParam('q'));
            $params = array_merge($params, $values);
            return Mage::getUrl('*/*/*', $params);
        } else {
            $params['_disable_filter']    = true;
            $baseUrl      = Mage::getUrl('*/*/*', $params);
            $urlSeparator = $helper->getConfigData('url_separator');
            return sprintf('%s/%s/%s', trim($baseUrl, '/'), $urlSeparator, implode('/', $filterParams));
        }
    }
}

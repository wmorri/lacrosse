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


class EcommerceTeam_Sln_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_PATH = 'catalog/layered_navigation';
    protected $_configCache = array();
    protected $_optionImageBasePath;
    protected $_optionImageBaseUrl;

    protected $_sliderJsLoaded = false;

    public function __construct()
    {
        $this->_optionImageBasePath    = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'attribute' . DS;
        $this->_optionImageBaseUrl     = Mage::getBaseUrl('media') . 'catalog' . '/' . 'attribute' . '/';
    }
    /**
     * Get config value
     *
     * @param string $xmlNode
     * @return string
     */
    public function getConfigData($xmlNode)
    {
        if(!isset($this->_configCache[$xmlNode])){
            $this->_configCache[$xmlNode] = Mage::getStoreConfig(self::CONFIG_PATH.'/'.$xmlNode);
        }
        return $this->_configCache[$xmlNode];
    }
    /**
     * Get config flag value
     *
     * @param string $xmlNode
     * @return boolean
     */
    public function getConfigFlag($xmlNode)
    {
        if(!isset($this->_configCache[$xmlNode])){
            $this->_configCache[$xmlNode] = Mage::getStoreConfigFlag(self::CONFIG_PATH.'/'.$xmlNode);
        }
        return $this->_configCache[$xmlNode];
    }

    /**
     *
     * Move option image from Temp directory to Media directory
     *
     * @param string $fileName
     * @param int $attributeId
     * @param int $optionId
     * @return string
     */
    public function moveImageFromTemp($fileName, $attributeId, $optionId)
    {
        $ioObject = new Varien_Io_File();
        $targetDirectory = $this->_optionImageBasePath . $attributeId . DS . $optionId;
        try {
            $ioObject->rmdir($targetDirectory, true);
            $ioObject->mkdir($targetDirectory, 0777, true);
            $ioObject->open(array('path'=>$targetDirectory));
        } catch (Exception $e) {
            return false;
        }

        $fileName   = trim($fileName, '.tmp');
        $targetFile = Varien_File_Uploader::getNewFileName($fileName);

        $path       = $targetDirectory . DS . $targetFile;
        $ioObject->mv(
            Mage::getSingleton('catalog/product_media_config')->getTmpMediaPath($fileName),
            $path
        );
        return $targetFile;
    }

    public function getOptionImageUrl($fileName, $attributeId, $optionId)
    {
        $subPath = $attributeId . DS . $optionId . DS . $fileName;
        $subURL  = $attributeId . '/' . $optionId . '/' . $fileName;
        if (is_file($this->_optionImageBasePath . $subPath)) {
            return $this->_optionImageBaseUrl . $subURL;
        }
        return false;
    }

    public function getCustomUrl($requestVar, $value, $singleMode = false)
    {
        /** @var $helper EcommerceTeam_Sln_Helper_Data */
        $helper       = Mage::helper('ecommerceteam_sln');
        /** @var $request EcommerceTeam_Sln_Model_Request */
        $request      = Mage::getSingleton('ecommerceteam_sln/request');
        $filterParams = array();
        $values = $request->getValue();

        if(empty($values)){
            $values = array();
        }

        if(!$singleMode && isset($values[$requestVar])){
            $values[$requestVar][] = $value;
            ksort($values);
        }else{
            $values[$requestVar] = array($value);
        }

        foreach($values as $requestVar=>$requestValue){
            if (!isset($requestValue['start']) && isset($requestValue['end'])) {
                sort($requestValue);
            }
            foreach($requestValue as $key=>$value){
                if($_value = $request->getFilterKeyById($requestVar, $value)){
                    $requestValue[$key] = $_value;
                }
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
            $baseUrl = Mage::getUrl('*/*/*', $params);
            $urlSeparator = $helper->getConfigData('url_separator');
            return sprintf('%s/%s/%s', trim($baseUrl, '/'), $urlSeparator, implode('/', $filterParams));
        }
    }

    /**
     * Get url for remove item from filter
     *
     * @param string $requestVar
     * @param null|string|int $removeValue
     * @return string
     */
    public function getRemoveUrl($requestVar, $removeValue = null)
    {
        /** @var $helper EcommerceTeam_Sln_Helper_Data */
        $helper       = Mage::helper('ecommerceteam_sln');
        /** @var $request EcommerceTeam_Sln_Model_Request */
        $request      = Mage::getSingleton('ecommerceteam_sln/request');
        $filterParams = array();
        if ($values = $request->getValue()) {
            ksort($values);
            foreach ($values as $_requestVar => $_requestValue) {
                if ($_requestVar == $requestVar) {
                    if ($removeValue) {
                        if (is_array($removeValue)) {
                            $value = sprintf('%d,%d', $removeValue['index'], $removeValue['range']);
                        } else {
                            $value = $removeValue;
                        }
                        if (false !== ($key = array_search($value, $_requestValue))) {
                            unset($_requestValue[$key]);
                        }
                    } else {
                        unset($values[$_requestVar], $_requestValue);
                    }
                }
                if (!empty($_requestValue)) {
                    if (!isset($requestValue['start']) && isset($requestValue['end'])) {
                        sort($requestValue);
                    }
                    foreach ($_requestValue as $key=>$value) {
                        if ($_value = $request->getFilterKeyById($_requestVar, $value)) {
                            $_requestValue[$key] = $_value;
                        }
                    }
                    $filterParams[] = $_requestVar;
                    $values[$_requestVar] = $filterParams[] = implode(',', $_requestValue);
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
            $baseUrl = Mage::getUrl('*/*/*', $params);
            $urlSeparator = $helper->getConfigData('url_separator');
            return sprintf('%s/%s/%s', trim($baseUrl, '/'), $urlSeparator, implode('/', $filterParams));
        }
    }
}

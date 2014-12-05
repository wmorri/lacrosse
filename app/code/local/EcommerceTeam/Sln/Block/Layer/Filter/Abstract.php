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

abstract class EcommerceTeam_Sln_Block_Layer_Filter_Abstract extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->getTemplate()) {
            $this->setTemplate('catalog/layer/filter.phtml');
        }
    }

    protected function _prepareFilter()
    {
        parent::_prepareFilter();
        switch ($this->_filter->getFrontendType()):
            case EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_DEFAULT:
            default:
                //use magento default template
            break;
            case EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_CHECKBOX:
                $this->_template = 'ecommerceteam/sln/layer/filter/checkbox.phtml';
            break;
            case EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_DROPDOWN:
                $this->_template = 'ecommerceteam/sln/layer/filter/dropdown.phtml';
            break;
            case EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_IMAGE:
                $this->_template = 'ecommerceteam/sln/layer/filter/image.phtml';
            break;
            case EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_INPUT:
                $this->_template = 'ecommerceteam/sln/layer/filter/input.phtml';
            break;
            case EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_SLIDER:
                $this->_template = 'ecommerceteam/sln/layer/filter/slider.phtml';
            break;
        endswitch;
    }
    /**
     * Retrieve filter items
     *
     * @return array
     */
    public function getAllItems()
    {
        return $this->_filter->getAllItems();
    }
    /**
     *
     * Return url without filter var
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        /** @var $request EcommerceTeam_Sln_Model_Request */
        $request        = Mage::getSingleton('ecommerceteam_sln/request');
        $requestVar     = $this->_filter->getRequestVar();
        $params         = array();
        $filterParams   = array();
        $values = $request->getValue();
        if (!empty($values) && is_array($values)) {
           unset($values[$requestVar]);
           ksort($values);
           foreach ($values as $_requestVar => $requestValue) {
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
                    $filterParams[] = implode(',', $requestValue);
                }
            }
        }

        $params['_use_rewrite']     = true;
        $params['_escape']          = true;
        $params['_nosid']            = true;

        if(Mage::app()->getFrontController()->getRequest()->getModuleName() == 'catalogsearch'){
            $params['_query'] = array('q'=>Mage::app()->getFrontController()->getRequest()->getParam('q'));
        }else{
            $params['_disable_filter']    = true;
        }

        $baseUrl = Mage::getUrl('*/*/*', $params);

        if(empty($filterParams)){
            $params['_query']['reset_filter'] = 1;
            return Mage::getUrl('*/*/*', $params);
        }

        if(Mage::app()->getFrontController()->getRequest()->getModuleName() == 'catalogsearch'){
            $params = array_merge($params, $filterParams);
            return Mage::getUrl('*/*/*', $params);
        }else{
            $params['_disable_filter']    = true;
            $helper = Mage::helper('ecommerceteam_sln');
            $urlSeparator = $helper->getConfigData('url_separator');
            return sprintf('%s/%s/%s', trim($baseUrl, '/'), $urlSeparator, implode('/', $filterParams));
        }
    }

    /**
     * Initialize filter model object
     *
     * @return Mage_Catalog_Block_Layer_Filter_Abstract
     */
    public function init()
    {
        $attribute    = $this->getAttributeModel();
        $registry_key = $this->_filterModelName . ($attribute ? ('_' . $attribute->getAttributeCode()) : '');
        $filter       = Mage::registry($registry_key);

        if ($filter) {
            $this->_filter = $filter;
            $this->_prepareFilter();
        } else {
            parent::_initFilter();
            Mage::register($registry_key, $this->_filter);
        }

        return $this;
    }

    public function startJavaScript()
    {
        if (!$this->getParentBlock()->getIsAjax()) {
            echo '<script type="text/javascript">';
        } else {
            ob_start();
        }
    }

    public function endJavaScript()
    {
        if (!$this->getParentBlock()->getIsAjax()) {
            echo '</script>';
        } else {
            $this->getParentBlock()->setJavaScript($this->getParentBlock()->getJavaScript() . ob_get_clean());
        }
    }

    public function canShow()
    {
        if($this->getAllItemsCount()){
            return true;
        }
        return false;
    }

    /**
     * Retrieve filter items count
     *
     * @return int
     */
    public function getAllItemsCount()
    {
        return $this->_filter->getAllItemsCount();
    }

    public function getComment(){
        return $this->_filter->getComment();
    }

    public function getRequestVar(){
        return $this->_filter->getRequestVar();
    }
}


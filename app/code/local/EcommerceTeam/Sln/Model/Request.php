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

class EcommerceTeam_Sln_Model_Request
    extends Mage_Core_Model_Abstract
{
    protected $_storeInSession    = false;

    protected $_params            = array();
    protected $_originalParams    = array();

    protected $_request;
    protected $_filterAliasByKey;
    protected $_filterAliasById;

    public function getFilterableAttributes()
    {
        if ($this->_request->getModuleName() == 'catalogsearch'){
            $filterableAttributes = Mage::getSingleton('catalogsearch/layer')->getFilterableAttributes();
        } else {
            $filterableAttributes = Mage::getSingleton('catalog/layer')->getFilterableAttributes();
        }
        return $filterableAttributes;
    }

    public function getFilterIdByKey($requestVar, $key)
    {
        if (isset($this->_filterAliasByKey[$requestVar][$key])){
            return $this->_filterAliasByKey[$requestVar][$key];
        }
    }

    public function getFilterKeyById($request_var, $id)
    {
        if (isset($this->_filterAliasById[$request_var][$id])){
            return $this->_filterAliasById[$request_var][$id];
        }
    }

    protected function _getCategories()
    {
        if (is_null($this->categories)) {
            if ($currentCategory = Mage::registry('current_category')){
                $childIds = $currentCategory->getResource()->getChildren($currentCategory, false);
                if (!empty($childIds)){
                    $collection = $currentCategory->getCollection()->addIdFilter($childIds);
                    $collection->addAttributeToSelect('name');
                    $collection->addAttributeToSelect('url_key');
                    $this->categories = $collection;
                } else {
                    $this->categories = false;
                }
            }
        }
        return $this->categories;
    }

    /**
     * Init attribute aliases
     *
     * @return void
     */
    protected function _initAliases()
    {
        $aliasCollection = Mage::getResourceModel('ecommerceteam_sln/attribute_collection');
        $aliasCollection->getSelect()->join(
                    array('attribute'=>$aliasCollection->getTable('eav/attribute')),
                    'main_table.attribute_id=attribute.attribute_id',
                    'attribute_code');

        $this->_filterAliasByKey   = array();
        $this->_filterAliasById    = array();

        foreach ($aliasCollection as $alias) {
            $this->_filterAliasByKey[$alias->getAttributeCode()][$alias->getUrlKey()]        = $alias->getOptionId();
            $this->_filterAliasById[$alias->getAttributeCode()][$alias->getOptionId()]    = $alias->getUrlKey();
        }

        if ($categories = $this->_getCategories()){
            foreach ($categories as $category) {
                $this->_filterAliasByKey['cat'][$category->getUrlKey()]    = $category->getEntityId();
                $this->_filterAliasById['cat'][$category->getEntityId()]    = $category->getUrlKey();
            }
        }
    }

    /**
     *
     */
    public function __construct()
    {
        $this->_request = Mage::app()->getFrontController()->getRequest();
        $this->_initAliases();
        $filterableAttributes = $this->getFilterableAttributes();
        if ($value = $this->_request->getParam('cat')) {
            $values = explode(',', $value);
            $originalValues = $values;

            foreach($values as $key=>$value){
                if ($_value = $this->getFilterIdByKey('cat', $value)){
                    $values[$key] = $_value;
                }
            }
            $this->_params['cat'] = $values;
            $this->_originalParams['cat'] = $originalValues;
        }
        foreach ($filterableAttributes as $attribute):
            $attribute_code = $attribute->getAttributeCode();
            $value = $this->_request->getParam($attribute_code);
            if (EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_INPUT == $attribute->getFrontendType()
                || EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_SLIDER == $attribute->getFrontendType()) {

                $start = (int) $this->_request->getParam($attribute_code . '_from');
                $end   = (int) $this->_request->getParam($attribute_code . '_to');
                if (!$start && !$end) {
                    $values = explode(',', $this->_request->getParam($attribute_code));
                    $start = isset($values[0]) ? $values[0] : 0;
                    $end   = isset($values[1]) ? $values[1] : 0;
                }

                if ($start > 0 || $end > 0) {
                    $value = array(
                        'start' => intval($start),
                        'end'   => intval($end),
                    );
                    $this->_params[$attribute_code] = $value;
                    $this->_originalParams[$attribute_code] = implode(',', $value);
                }


            } elseif (null !== $value && '' !== $value) {
                if ($attribute->getFrontendInput() == 'price') {
                    $_value         = array();
                    $_values        = explode(',', $value);
                    $originalValues = $_values;
                    $length         = count($_values);

                    $i = 0;

                    while ($i < $length){
                        $_value[] = sprintf('%d,%d', $_values[$i], $_values[$i+1]);
                        $i += 2;
                    }

                    $this->_params[$attribute_code] = $_value;
                    $this->_originalParams[$attribute_code] = $originalValues;

                } else {
                    $values         = explode(',', $value);
                    $originalValues = $values;
                    foreach ($values as $key=>$value){
                        if($_value = $this->getFilterIdByKey($attribute_code, $value)){
                            $values[$key] = $_value;
                        }
                    }
                    $this->_params[$attribute_code]         = $values;
                    $this->_originalParams[$attribute_code] = $originalValues;
                }
            }
        endforeach;

        if ($this->_storeInSession) {
            if ($category = Mage::getSingleton('catalog/layer')->getCurrentCategory()){
                if ($this->_request->getParam('reset_filter')>0){
                    Mage::getSingleton('customer/session')->setData('layered_state_category_'.$category->getEntityId(), null);
                } elseif ($category->getLevel() > 1) {
                    if (!empty($this->_params)) {
                        Mage::getSingleton('customer/session')->setData('layered_state_category_'.$category->getEntityId(), Zend_Json::encode(array('params'=>$this->_params, 'original_params'=>$this->_originalParams)));
                    } else {
                        if($saved = Mage::getSingleton('customer/session')->getData('layered_state_category_'.$category->getEntityId())){
                            $saved = Zend_Json::decode($saved);
                            $this->_params         = $saved['params'];
                            $this->_originalParams = $saved['original_params'];
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * Return current filter value(s)
     *
     * @param string $requestVar
     * @return array|string
     */
    public function getValue($requestVar = '')
    {
        if ($requestVar === '') {
            return $this->_params;
        } else {
            if (isset($this->_params[$requestVar])) {
                return $this->_params[$requestVar];
            }
        }
    }
    public function getOriginalValue($requestVar = '')
    {
        if ($requestVar === '') {
            return $this->_originalParams;
        } else {
            if (isset($this->_originalParams[$requestVar])) {
                return $this->_originalParams[$requestVar];
            }
        }
    }
}

<?php

class EcommerceTeam_Sln_Model_Url_Rewrite
    extends Mage_Core_Model_Url_Rewrite
{

    public function rewrite(Zend_Controller_Request_Http $request = null,
        Zend_Controller_Response_Http $response = null)
    {
        /** @var $helper EcommerceTeam_Sln_Helper_Data */
        $helper = Mage::helper('ecommerceteam_sln');

        if (is_null($request)) {
            $request = Mage::app()->getFrontController()->getRequest();
        }

        $separator   = $helper->getConfigData('url_separator');
        $requestPath = trim($request->getPathInfo(), '/');
        $path        = explode('/', $requestPath);
        $filterKey   = array_search($separator, $path);

        if ($filterKey !== false):
            $filterParams = array_slice($path, $filterKey+1);
            if (array_search('catalogsearch', $path) !== false) {
                $q = '';
                if (isset($_REQUEST['q'])) {
                    $q = $_REQUEST['q'];
                } else {
                    $qPosition = array_search('q', $path);
                    if($qPosition !== false){
                        $q = $path[$qPosition+1];
                    }
                }
                $request->setPathInfo(sprintf('catalogsearch/result/index/q/%s/%s', urlencode($q), implode('/', $filterParams)));
            } else {
                if ($filterKey) {
                    $categoryUrlKey = implode('/', array_slice($path, 0, $filterKey));
                } else {
                    $categoryUrlKey = implode('/', $path);
                }
                if ($filterKey && $categoryUrlKey) {
                    if (Mage::getStoreConfig("catalog/seo/category_url_suffix") == "/"){
    				   $categoryUrlKey = $categoryUrlKey . '/';
					}    
                    $attribute      = Mage::getModel('eav/entity_attribute')->loadByCode(Mage::getResourceModel('catalog/category')->getTypeId(), 'url_path');
                    $attributeTable = $attribute->getBackendTable();
                    $attributeId    = $attribute->getAttributeId();
                    $connection     = Mage::getSingleton('core/resource')->getConnection('core_read');

                    $select = new Varien_Db_Select($connection);
                    $select->from($attributeTable, array('entity_id'));
                    $select->where('attribute_id = ?', $attributeId);
                    $select->where('value = ?', $categoryUrlKey);
                    $select->where('store_id = ?', Mage::app()->getStore()->getId());
                    $select->limit(1);

                    $categoryId = $connection->fetchOne($select);
                    if (!$categoryId){
			           $select = new Varien_Db_Select($connection);
                       $select->from($attributeTable, array('entity_id'));
                       $select->where('attribute_id = ?', $attributeId);
                       $select->where('value = ?', $categoryUrlKey);
		               $select->limit(1);
		               $categoryId = $connection->fetchOne($select);
		            }
                    if ($categoryId > 0) {
                        $request->setPathInfo(sprintf('catalog/category/view/id/%d/%s', $categoryId, implode('/', $filterParams)));
                        $request->setAlias('rewrite_request_path', $categoryUrlKey);
                        return true;
                    }
                }
            }
        endif;
        return parent::rewrite($request, $response);
    }
}

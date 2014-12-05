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

class EcommerceTeam_Sln_Model_Observer
{
    protected $_categories;
    /**
     *
     * Check for ajax request
     *
     * @return void
     */
    public function initAjax()
    {
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');
        if ($layout) {
            $request = Mage::app()->getFrontController()->getRequest();
            if ((bool)$request->getParam('is_ajax', false)) {
                $_SERVER['REQUEST_URI'] = preg_replace('/is_ajax=\d/i', '', $request->getRequestUri());
                Mage::app()->getFrontController()->getResponse()->setHeader('content-type', 'application/json');
                $layout->removeOutputBlock('root');

                $blocks = $layout->getAllBlocks();
                $layeredBlocks = array_filter($blocks, create_function('$block', 'if(preg_match("/^ecommerceteam_seo_navigation\w*$/i", $block->getNameInLayout()) > 0) { return true; }'));

                $productListBlock = $layout->getBlock('product_list');
                if(!$productListBlock){
                    $productListBlock = $layout->getBlock('search_result_list');
                }
                $headBlock = $layout->getBlock('head');
                $title = '';
                if ($headBlock){
                    $title = $headBlock->getTitle();
                }
                if(count($layeredBlocks) > 0 && $productListBlock):
                    $slnAjaxContainer = $layout->createBlock('core/template', 'ecommerceteam_sln_ajax');
                    $layeredBlocksHtml = array();
                    foreach ($layeredBlocks as $block) {
                        $block->setIsAjax(true);
                        $layeredBlocksHtml[$block->getBlockId()] = array(
                            'html'   => $block->toHtml(),
                            'script' => $block->getJavaScript()
                        );
                    }
                    $slnAjaxContainer->setData('navigation_block_html', $layeredBlocksHtml);
                    $slnAjaxContainer->setData('product_list_block_html', $productListBlock->toHtml());
                    $slnAjaxContainer->setData('page_title', $title);
                    $layout->addOutputBlock('ecommerceteam_sln_ajax', 'toJson');
                endif;
            }
        }
    }

    public function getCategories()
    {
        if (is_null($this->_categories)) {
            if ($currentCategory = Mage::registry('current_category')) {
                $childIds = $currentCategory->getResource()->getChildren($currentCategory, false);
                if (!empty($childIds)) {
                    $collection = $currentCategory->getCollection()->addIdFilter($childIds);

                    $collection->addAttributeToSelect('name');
                    $collection->addAttributeToSelect('url_key');

                    $this->_categories = $collection;
                } else {
                    $this->_categories = false;
                }
            }
        }
        return $this->_categories;
    }
    public function setPageTitle($event)
    {
        $moduleName        = Mage::app()->getFrontController()->getRequest()->getModuleName();
        $controllerName    = Mage::app()->getFrontController()->getRequest()->getControllerName();
		$store             = Mage::app()->getStore();
		
        if (!Mage::registry('layer_loaded') || $moduleName == 'catalogsearch' && $controllerName == 'advanced') {
            return;
        }
        if ($headBlock = $event->getLayout()->getBlock('head')) {
            $_request = Mage::getSingleton('ecommerceteam_sln/request'); 
            $activeFilters = $_request->getValue();

            if (!empty($activeFilters)) {
                $attributes = $_request->getFilterableAttributes();
                if (!empty($attributes) && ($title = $headBlock->getTitle())) {
					$title = str_replace(Mage::getStoreConfig('design/head/title_suffix'),"",$title);
                    $filtersTitle = array();
                    foreach ($activeFilters as $code=>$value) {
                        $_labelValues = array();
                        if ($model = $attributes->getItemByColumnValue('attribute_code', $code)) {
                            $_labelValues = array();
                            if ($model->getFrontendInput() == 'select' || $model->getFrontendInput() == 'multiselect') {
                                foreach ((array)$value as $_value) {
                                    $_labelValues[] = ($model->getSource()->getOptionText($_value));
                                }
                            } elseif ($model->getFrontendInput() == 'price') {
                                if (isset($value['start']) && isset($value['end'])) {
                                   $_labelValues[] = Mage::helper('core')->currency($value['start'], true, false) .' - '. Mage::helper('core')->currency($value['end'], true, false);
                                }
                                foreach ((array)$value as $_value) {
                                    $_value = explode(',', $_value);
                                    if (isset($_value[0]) && isset($_value[1])) {
                                        $_labelValues[] = $store->formatPrice(($_value[0]-1)*$_value[1],false) .' - '. $store->formatPrice(($_value[0])*$_value[1],false);
                                    }
                                }
                            }

                            $filtersTitle[] = $model->getFrontendLabel() . ': ' . implode(', ', $_labelValues);

                        } elseif ($code == 'cat') {
                            $categories = $this->getCategories();
                            $_labelValues = array();

                            if ($categories && !empty($categories)) {

                                foreach ((array)$value as $id) {
									if ($cat = $categories->getItemById($id)){
                                        $_labelValues[] = $cat->getName();
                                    }
                                }
                                $title = trim(implode(', ', $_labelValues)). ' - ' .  $title;
                            }
                        }
                    }
                    if ($filtersTitle) {
                        $headBlock->setTitle($title.' with '. implode(' and ', $filtersTitle));
                    } else {
                        $headBlock->setTitle($title);
                    }
                }
                if ($category = Mage::registry('current_category')){
			    	$headBlock->removeItem('link_rel',$category->getUrl());
			    }
            }

        }
    }
    public function loadAttributeData($event)
    {
        $attribute    = $event->getAttribute();
        $attribute_id = (int)$attribute->getAttributeId();
        $connection   = Mage::getSingleton('core/resource')->getConnection('read');
        $table        = Mage::getSingleton('core/resource')->getTableName('ecommerceteam_sln_attribute_data');

        $select = new Varien_Db_Select($connection);
        $select->from($table, array('group_id', 'frontend_type', 'comment'));
        $select->where('attribute_id = ?', $attribute_id);

        $data = $connection->fetchRow($select);
        //$data = $connection->fetchRow("SELECT `group_id`, `frontend_type`, `comment` FROM {$table} WHERE `attribute_id` = {$attribute_id};");;

        if ($data && is_array($data) && !empty($data)) {
            $attribute->addData($data);
        }
    }

    public function saveAttributeData($event)
    {
        /** @var $connection Varien_Db_Adapter_Pdo_Mysql */
        $connection  = Mage::getSingleton('core/resource')->getConnection('read');
        $table       = Mage::getSingleton('core/resource')->getTableName('ecommerceteam_sln_attribute_data');

        $attributeId     = (int)$event->getAttribute()->getAttributeId();
        $groupId         = addslashes($event->getAttribute()->getData('group_id'));
        $frontendType    = addslashes($event->getAttribute()->getData('frontend_type'));
        $comment         = $event->getAttribute()->getData('comment');

        $connection->insertOnDuplicate(
            $table,
            array(
                'attribute_id' => $attributeId,
                'group_id' => $groupId,
                'frontend_type' => $frontendType,
                'comment' => $comment,
            )
        );
    }
}

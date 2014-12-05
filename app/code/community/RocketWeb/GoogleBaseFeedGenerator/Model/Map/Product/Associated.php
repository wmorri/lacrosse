<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @copyright  Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

class RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Associated extends RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract
{
	public function mapColumnDescription($params = array())
	{
		$args = array('map' => $params['map']);
    	$value = "";
    	
    	switch ($this->getConfigVar('associated_products_description', 'columns')) {
    		case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsdesc::FROM_ASSOCIATED:
    			$value = $this->getCellValue($args);
    			break;
    		case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsdesc::FROM_CONFIGURABLE:
    			$value = $this->getParentMap()->mapColumn('description');
    			break;
    		case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsdesc::FROM_CONFIGURABLE_ASSOCIATED:
    			$value = $this->getParentMap()->mapColumn('description');
    			if ($value == "")
    				$value = $this->getCellValue($args);
    			break;
    		case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsdesc::FROM_ASSOCIATED_CONFIGURABLE:
    			$value = $this->getCellValue($args);
    			if ($value == "")
    				$value = $this->getParentMap()->mapColumn('description');
    			break;
    		
    		default:
    			$value = $this->getCellValue($args);
    			if ($value == "")
    				$value = $this->getParentMap()->mapColumn('description');
    		
    	}
		
		return $value;
	}
	
	public function mapColumnLink($params = array())
	{
		$args = array('map' => $params['map']);
		$product = $this->getProduct();
    	$value = "";
    	
    	switch ($this->getConfigVar('associated_products_link', 'columns')) {
    		case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodslink::FROM_CONFIGURABLE:
    			$value = $this->getParentMap()->mapColumn('link');
    			if ($this->getConfigVar('associated_products_link_add_unique', 'columns'))
    				$value = $this->addUrlUniqueParams($value, $product, $this->getParentMap()->getConfigurableAttributeCodes());
    			break;
    		case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodslink::FROM_ASSOCIATED_CONFIGURABLE:
    			if ($product->isVisibleInSiteVisibility()) {
		    		$value = $this->getCellValue($args);
		    	} else {
    				$value = $this->getParentMap()->mapColumn('link');
    				if ($this->getConfigVar('associated_products_link_add_unique', 'columns'))
    					$value = $this->addUrlUniqueParams($value, $product, $this->getParentMap()->getConfigurableAttributeCodes());
		    	}
    			break;
    		
    		default:
    			$value = $this->getParentMap()->mapColumn('link');
    			if ($this->getConfigVar('associated_products_link_add_unique', 'columns'))
    				$value = $this->addUrlUniqueParams($value, $product, $this->getParentMap()->getConfigurableAttributeCodes());
    	}
    	
		return $value;
	}
	
	protected function addUrlUniqueParams($value, $product, $codes)
	{
		$params = array();
		foreach ($codes as $attribut_code) {
			$data = $product->getData($attribut_code);
			if (empty($data)) {
				$this->skip = true;
	    		if ($this->getConfigVar('log_skip')) {
					$this->log(sprintf("product id %d product sku %s, can't fetch data from attribute: '%s' ('%s') to make create url.", $this->getProduct()->getId(), $this->getProduct()->getSku(), $attribut_code, $data));
				}
				return $value;
			}
			$params[$attribut_code] = $data;
		}
		$urlinfo = parse_url($value);
		if ($urlinfo !== false) {
			if (isset($urlinfo['query'])) {
				$urlinfo['query'] .= '&'.http_build_query($params);
			} else {
				$urlinfo['query'] = http_build_query($params);
			}
			$new = "";
			foreach ($urlinfo as $k => $v) {
				if ($k == 'scheme') {
					$new .= $v.'://';
				} elseif ($k == 'port') {
					$new .= ':'.$v;
				} elseif ($k == 'query') {
					$new .= '?'.$v;
				} else {
					$new .= $v;
				}
			}
			if (parse_url($new) === false) {
				$this->skip = true;
	    		if ($this->getConfigVar('log_skip')) {
					$this->log(sprintf("product id %d product sku %s, failed to form new url: %s from old url %s.", $this->getProduct()->getId(), $this->getProduct()->getSku(), $new, $value));
				}
			} else {
				$value = $new;
			}
		}
		
		return $value;
	}
	
	public function mapColumnImageLink($params = array())
	{
		$args = array('map' => $params['map']);
    	$value = "";
    	
    	switch ($this->getConfigVar('associated_products_image_link_configurable', 'columns')) {
    		case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsimagelink::FROM_CONFIGURABLE:
    			$value = $this->getParentMap()->mapColumn('image_link');
    			break;
    		case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsimagelink::FROM_ASSOCIATED:
    			$value = $this->getCellValue($args);
    			break;
    		case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsimagelink::FROM_ASSOCIATED_CONFIGURABLE:
		    	$value = $this->getCellValue($args);
		    	if ($value == "") {
		    		$value = $this->getParentMap()->mapColumn('image_link');
		    	}
    			break;
    		case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsimagelink::FROM_CONFIGURABLE_ASSOCIATED:
		    	$value = $this->getParentMap()->mapColumn('image_link');
		    	if ($value == "") {
		    		$value = $this->getCellValue($args);
		    	}
    			break;
    		
    		default:
    			$value = $this->getCellValue($args);
		    	if ($value == "") {
		    		$value = $this->getParentMap()->mapColumn('image_link');
		    	}
    	}
    	
		return $value;
	}
	
	/**
	 * By default no additional images for associated products.
	 *
	 * @todo Allow additional images like mapColumnImageLink. $product->getMediaGalleryImages() does not work here.
	 * @param array $params
	 * @return string
	 */
	public function mapColumnAdditionalImageLink($params = array())
	{
		$args = array('map' => $params['map']);
    	$value = "";
    	
    	return $value;
	}
	
	public function getPrice($product = null)
	{
		if (is_null($product)) {
			$product = $this->getProduct();
		}
		
		$price = $this->getParentMap()->getCacheAssociatedPrice($product->getId());
		return $price;
	}
	
	public function mapColumnSalePrice($params = array())
	{
		$map = $params['map'];
    	$product = $this->getProduct();
    	/** @var $product Mage_Catalog_Model_Product */

    	$cell = "";
    	if (!$this->getParentMap()->hasSpecialPrice($this->getParentMap()->getProduct(), $this->getParentMap()->getSpecialPrice($this->getParentMap()->getProduct())))
    		return $cell;
		
    	$helper = Mage::helper('googlebasefeedgenerator/tax');
    	/** @var $helper Mage_Tax_Helper_Data */
    	/* 0 - excluding tax
    	   1 - including tax */
    	$priceIncludesTax = ( $helper->priceIncludesTax($this->getStoreId()) ? true : false);
    	$includingTax = ($this->getConfigVar('add_tax_to_price', 'columns') ? true : false);
    	$special_price = $this->getParentMap()->getCacheAssociatedPrice($product->getId()) - $this->getParentMap()->getPrice($this->getParentMap()->getProduct()) + $this->getParentMap()->getSpecialPrice($this->getParentMap()->getProduct());
    	$price = $helper->getPrice($product, $special_price, $includingTax, false, false, null, $this->getStoreId(), $priceIncludesTax);
    	$cell = $price;
    	
    	if ($cell <= 0) {
    		if ($this->getConfigVar('log_skip')) {
    			$this->log(sprintf("product id %d product sku %s, skipped - product has price 0 = '%s'.", $product->getId(), $product->getSku(), $cell));
    		}
	    	$this->skip = true;
    	}

    	$cell = $this->cleanField($cell);
    	return $cell;
	}
	
	public function mapColumnSalePriceEffectiveDate($params = array())
	{
		$args = array('map' => $params['map']);
    	$value = "";
    	$value = $this->getParentMap()->mapColumn('sale_price_effective_date');
		return $value;
	}
	
	public function mapColumnAvailability($params = array())
	{
		$args = array('map' => $params['map']);
    	$value = "";
    	$value = $this->getParentMap()->mapColumn('availability');
    	// gets out of stock if parent is out of stock
    	if (strcasecmp($this->getConfig()->getOutOfStockStatus(), $value) == 0)
    		return $value;
    	
    	$value = $this->getCellValue($args);
    	
		return $value;
	}
	
	public function mapColumnBrand($params = array())
	{
		$args = array('map' => $params['map']);
    	$value = "";
    	
    	// get value from parent first
    	$value = $this->getParentMap()->mapColumn('brand');
    	if ($value != "")
    		return $value;
    	
    	$value = $this->getCellValue($args);
		
		return $value;
	}
	
	public function mapColumnGoogleProductCategory($params = array())
	{
		$args = array('map' => $params['map']);
    	$value = "";
    	
    	// get value from parent first
    	$value = $this->getParentMap()->mapColumn('google_product_category');
    	if ($value != "")
    		return html_entity_decode($value);
    	
    	if ($value == "") {
    		$value = $this->getCellValue($args);
    		if ($value != "") {
    			return html_entity_decode($value);
    		}
    	}
    	
    	$map_by_category = $this->getConfig()->getMapCategorySorted('google_product_category_by_category', $this->getStoreId());
    	$category_ids = $this->getProduct()->getCategoryIds();
    	if (empty($category_ids))
    		$category_ids = $this->getParentMap()->getProduct()->getCategoryIds();
    	if (!empty($category_ids) && count($map_by_category) > 0) {
    		foreach ($map_by_category as $arr) {
    			if (array_search($arr['category'], $category_ids) !== false) {
    				$value = $arr['value'];
    				break;
    			}
    		}
    	}
    	if ($value != "")
    		return html_entity_decode($value);
    	
		return html_entity_decode($value);
	}
	
	public function mapColumnProductType($params = array())
	{
		$args = array('map' => $params['map']);
    	$value = "";
    	
    	// get value from parent first
    	$value = $this->getParentMap()->mapColumn('product_type');
    	if ($value != "")
    		return html_entity_decode($value);
    	
    	if ($value == "") {
    		$value = $this->getCellValue($args);
    		if ($value != "") {
    			return html_entity_decode($value);
    		}
    	}
    	
    	$map_by_category = $this->getConfig()->getMapCategorySorted('product_type_by_category', $this->getStoreId());
    	$category_ids = $this->getProduct()->getCategoryIds();
    	if (empty($category_ids))
    		$category_ids = $this->getParentMap()->getProduct()->getCategoryIds();
    	if (!empty($category_ids) && count($map_by_category) > 0) {
    		foreach ($map_by_category as $arr) {
    			if (array_search($arr['category'], $category_ids) !== false) {
    				$value = $arr['value'];
    				break;
    			}
    		}
    	}
    	
    	if ($value != "")
    		return html_entity_decode($value);
		
		return html_entity_decode($value);
	}
}
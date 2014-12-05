<?php
class SMDesign_ColorswatchProductList_GetController extends Mage_Core_Controller_Front_Action {
	function indexAction() {
		$this->norouteAction();
	}
	
	function imageAction() {

		$selection = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('selection', '{}'));
		$attributeId = $this->getRequest()->getParam('attribute_id');
		$optionId = $this->getRequest()->getParam('option_id');
		$productId = $this->getRequest()->getParam('product_id');
		$imageSelector = $this->getRequest()->getParam('image_selector', '.product-img-box .product-image img');
		
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;
		if (!isset($modulesArray['SMDesign_SMDZoom']) || (isset($modulesArray['SMDesign_SMDZoom']) && $modulesArray['SMDesign_SMDZoom']->active == false)) {
			$productListConfig['zoom_installed'] = false;
		}else{
			$productListConfig['zoom_installed'] = true;
		}
		$productListConfig['enable_zoom'] = (bool)Mage::getStoreConfig('smdesign_colorswatch/product_list/enable_zoom');	
		$productListConfig['use_zoom'] = $productListConfig['enable_zoom'] && $productListConfig['zoom_installed'];
		
		$_product = Mage::getModel('catalog/product')->load($productId);
		
		if (!$_product->getId()) {
			$this->norouteAction();
			return;
		}
		
		$usedAttributeIds = $_product->getTypeInstance(true)->getUsedProductAttributeIds($_product);

		$selectedAttributeCode = $_product->getTypeInstance(true)->getAttributeById($attributeId, $_product)->getAttributeCode();
		
		$allProducts = $_product->getTypeInstance(true)->getUsedProducts(null, $_product);
		foreach ($allProducts as $product) {
		    if ($product->isSaleable() && $product->getData($selectedAttributeCode) == $optionId) {
		        $products[] = $product;
		    }
		}
		
		if (count($products) > 1) {
			$attributes = Mage::helper('core')->decorateArray($_product->getTypeInstance(true)->getConfigurableAttributes($_product));
			$tmpAttCode = array();
			
			foreach ($attributes as $attribute) {
				$tmpAttCode[$attribute->getProductAttribute()->getAttributeId()] = $attribute->getProductAttribute()->getAttributeCode();
			}
			
//			foreach ($selection as $aId=>$oId) {
//				foreach ($products as $key=>$simpleProduct) {
//					if ($simpleProduct->getData($tmpAttCode[$aId]) != $selection[$aId]) {
//						unset($products[$key]);
//					}
//				}
//			}
		}
		

		$newWidth = $imgElementWidth = 	$this->getRequest()->getParam('img_width', null);
		$newHeight = $imgElementHeight = $this->getRequest()->getParam('img_height', null);
		/* calculate dimensions */
		$productListConfig['list_image_width'] = Mage::getStoreConfig('smdesign_colorswatch/product_list/list_image_width');	
		$productListConfig['list_image_height'] = Mage::getStoreConfig('smdesign_colorswatch/product_list/list_image_height');
		$productListConfig['zoom_ratio'] = intval(Mage::getStoreConfig('smdesign_smdzoom/zoom/zoom_ratio'));
		$productListConfig['upscale_image_width'] = $productListConfig['zoom_ratio'] * $productListConfig['list_image_width'];
	    $productListConfig['upscale_image_height'] = $productListConfig['zoom_ratio'] * $productListConfig['list_image_height'];
		
		$images = array();
		foreach ($products as $simpleProduct) {
			$simpleProduct->load();
			$simpleProductImages = $simpleProduct->getMediaGalleryImages();
			if (count($simpleProductImages)) {
				foreach ($simpleProductImages as $_image) {
					if ($productListConfig['use_zoom']) {
						$cImage = Mage::helper('catalog/image')->init($simpleProduct, 'image');
						$width = $cImage->getOriginalWidth();
						$height = $cImage->getOriginalHeight();
						
						$wRatio = $width/$imgElementWidth;
						$hRatio = $height/$imgElementHeight;
						$ratio = max($wRatio, $hRatio);
						
						if ($wRatio > $hRatio) {
							$newWidth = $width;
							$newHeight = ($width * $imgElementHeight / $imgElementWidth );
						} else {
							$newHeight = $height;
							$newWidth = ($height * $imgElementWidth / $imgElementHeight );
						}
					}
					$images[] = array(
						'label'=> $_image->getLabel(),
						'image'=> sprintf(Mage::helper('catalog/image')->init($simpleProduct, 'image', $_image->getFile())->resize($productListConfig['list_image_width'], $productListConfig['list_image_height'])),
						'big_image'=> sprintf(Mage::helper('catalog/image')->init($simpleProduct, 'image', $_image->getFile())->resize($productListConfig['upscale_image_width'], $productListConfig['upscale_image_height']))
					);
				}
			}
		}
		
		if (count($images) == 0) {
			$images[0]['big_image'] = sprintf(Mage::helper('catalog/image')->init($_product, 'image')->resize($productListConfig['upscale_image_width'], $productListConfig['upscale_image_height']));
			$images[0]['image'] = sprintf(Mage::helper('catalog/image')->init($_product, 'image')->resize($productListConfig['list_image_width'], $productListConfig['list_image_height']));
			$images[0]['label'] = '';
		}

		
?>
	try {
		$$(".colorswatch-attribute-list-<?php echo $attributeId; ?>-<?php echo $productId; ?> li").each( function(elementLi, index) {
			Element.removeClassName(elementLi, 'active');
		} );
		Element.addClassName($$(".item-<?php echo $productId; ?> li.colorswatch-<?php echo $attributeId; ?>-<?php echo $optionId; ?>").first(), 'active');
	} catch (e) {
		<?php /* unable to add/remove active class from LI element */ ?>
	}

	try {
		$$('.more-swatches-available-<?php echo $productId?>').each(
			function(element,index) {
				element.href = '<?php echo  $_product->getProductUrl() ?>?<?php echo $selectedAttributeCode?>=<?php echo $optionId?>';
			}
		);
	} catch (e) {

	}


	try {
		$$('.item-<?php echo $productId?> a.product-image')[0].href = '<?php echo  $_product->getProductUrl() ?>?<?php echo $selectedAttributeCode?>=<?php echo $optionId?>';		
	} catch (e) {
		//alert("'.item-<?php echo $productId?> a.product-image'");
	}

	try {
		$$('.item-<?php echo $productId?> h2.product-name a')[0].href = '<?php echo  $_product->getProductUrl() ?>?<?php echo $selectedAttributeCode?>=<?php echo $optionId?>';
	} catch (e) {
		//alert("'.item-<?php echo $productId?> h2.product-name a'");
	}

	try {
		$$('.item-<?php echo $productId?> a.link-learn')[0].href = '<?php echo  $_product->getProductUrl() ?>?<?php echo $selectedAttributeCode?>=<?php echo $optionId?>';
	} catch (e) {

	}


	try {
		$$('.item-<?php echo $productId?> .product-image img')[0].src = '<?php echo  $images[0]['image']?>';
		$$('.item-<?php echo $productId?> a.product-image')[0].rel = '<?php echo  $images[0]['big_image']?>';
		<?php if ($productListConfig['use_zoom']) : ?>
		SMDesignsmdzoomPreloader = new SMDesignsmdzoomPreload({showPreloader:true});
		SMDesignsmdzoomPreloader.setImage('/skin/frontend/default/default/images/smdzoom/smdzoom_loading.gif');
		SMDesignsmdzoomPreloader.showPerload( $$('.item-<?php echo $productId?> .product-image img').first() );
		<?php endif; ?>
	} catch (e) {
	//alert(e);
		//alert("'.item-<?php echo $productId?> a.product-image'");
	}


	try {
		$$('.item-<?php echo $productId?> button.btn-configure').first().onclick = function() {
			setLocation("<?php echo  $_product->getProductUrl() ?>?<?php echo $selectedAttributeCode?>=<?php echo $optionId?>");
		}
	} catch (e) {

	}
	
	<?php 
	if (!in_array(false, $selection) && count($usedAttributeIds) == count($selection) ) : 

		$supperAttributeSelection = '';
		foreach($selection as $aid=>$oVal) {
			$supperAttributeSelection .= "&super_attribute[$aid]=$oVal";
		} ?>
	try {
		$$('.item-<?php echo $productId?> button.btn-cart').first().onclick = function() {
			setLocation("<?php echo Mage::getUrl("checkout/cart/add/")?>?product=<?php echo $productId; ?>&qty=1<?php echo $supperAttributeSelection ?>");
		}
	} catch (e) {

	}
	<?php endif; ?>

SMDesignColorswatchPreloader.removePerload($$('<?php echo $imageSelector?>')[0]);
<?php
	}
}
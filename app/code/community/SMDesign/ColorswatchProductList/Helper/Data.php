<?php 
class SMDesign_ColorswatchProductList_Helper_Data extends Mage_Core_Helper_Abstract {
	protected $_model;
	public $config;
	

	function setModel($model) {
		$this->_model = $model;
	}
	
	function getModel() {
		
		return $this->_model;
	}
	
	function __construct(){
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;

		$this->config['zoom_installed'] = false;
		if ( isset($modulesArray['SMDesign_SMDZoom']) ) {
			$SMDesignSMDZoomModuleConfig = $modulesArray['SMDesign_SMDZoom']->asArray();
			if ((string)$SMDesignSMDZoomModuleConfig['active'] === 'true') {
				$this->config['zoom_installed'] = true;
			}
		}

		$this->config['enabled'] = Mage::getStoreConfig('smdesign_colorswatch/product_list/enabled');	
		$this->config['max_swatches'] = Mage::getStoreConfig('smdesign_colorswatch/product_list/max_swatches');	
		$this->config['enable_zoom'] = (bool)Mage::getStoreConfig('smdesign_colorswatch/product_list/enable_zoom');	
		$this->config['list_image_width'] = Mage::getStoreConfig('smdesign_colorswatch/product_list/list_image_width');	
		$this->config['list_image_height'] = Mage::getStoreConfig('smdesign_colorswatch/product_list/list_image_height');
		$this->config['swatch_load_type'] = Mage::getStoreConfig('smdesign_colorswatch/general/swatch_load_type');
		
		$this->config['use_zoom'] = $this->config['enable_zoom'] && $this->config['zoom_installed'];
		$this->config['swatch_image_width']  = ((int)Mage::getStoreConfig('smdesign_colorswatch/general/swatch_image_size_width') > 0) ? (int)Mage::getStoreConfig('smdesign_colorswatch/general/swatch_image_size_width') : 30;
		$this->config['swatch_image_height'] = ((int)Mage::getStoreConfig('smdesign_colorswatch/general/swatch_image_size_height') > 0) ? (int)Mage::getStoreConfig('smdesign_colorswatch/general/swatch_image_size_height') : 30;
	}
	
	public function setZoomType($zoomType = 0){
		$this->config['zoom_type'] = $zoomType;
		if ($this->config['use_zoom']) {
			$zoomConfig['image_width'] 		= $this->config['list_image_width'];
	    	$zoomConfig['image_height'] 	= $this->config['list_image_height'];
	    	$zoomConfig['wrapper_width'] 	= $this->config['list_image_width'];
	    	$zoomConfig['wrapper_height'] 	= $this->config['list_image_height'];
	    	$zoomConfig['wrapper_offset_left'] = 10;
	    	$zoomConfig['wrapper_offset_top']  =12;
	    	$zoomConfig['zoom_ratio'] 		= intval(Mage::getStoreConfig('smdesign_smdzoom/zoom/zoom_ratio'));
			
			if ($zoomConfig['zoom_ratio'] == "" || $zoomConfig['zoom_ratio'] == 0 || $zoomConfig['zoom_ratio'] == 1) {
	    		$zoomConfig['zoom_ratio'] = 2;
	    	}
	    	
	    	switch ($zoomType){
	    		default:
		    	case 0:
		    		/* outside */
		    		$ratioModifierWidth = 0;
		    		$ratioModifierHeight = 0;
		    		$zoomConfig['wrapper_width'] = $zoomConfig['image_width'] + $zoomConfig['image_width'] /2;
		    		$zoomConfig['wrapper_height'] = $zoomConfig['image_height'] + $zoomConfig['image_height'] /2;
		    		if ($zoomConfig['image_width'] * $zoomConfig['zoom_ratio'] <= $zoomConfig['wrapper_width']) {
		    			$ratioModifierWidth = intval($zoomConfig['wrapper_width'] / ($zoomConfig['image_width'] * $zoomConfig['zoom_ratio']) );
		    		}
		    		if ($zoomConfig['image_height'] * $zoomConfig['zoom_ratio'] <= $zoomConfig['wrapper_height']) {
		    			$ratioModifierHeight = intval($zoomConfig['wrapper_height'] / ($zoomConfig['image_height'] * $zoomConfig['zoom_ratio']) );
		    		}
		    		$zoomConfig['zoom_ratio'] = $zoomConfig['zoom_ratio'] + max($ratioModifierWidth,$ratioModifierHeight);
		    		$zoomConfig['wrapper_offset_left'] 	= 10;
	    			$zoomConfig['wrapper_offset_top'] 	= 12;
		    	break;
		    	case 2:
		    		/* full */
		    		$zoomConfig['show_zoom_effect'] = "none";
	    			$zoomConfig['hide_zoom_effect'] = "none";
	    			$zoomConfig['wrapper_offset_left'] 	= 0;
	    			$zoomConfig['wrapper_offset_top'] 	= 0;
		    	break;
	    	}
		    
	    	$this->config['zoom_ratio'] = $zoomConfig['zoom_ratio'];
	    	$this->config['wrapper_offset_left'] = $zoomConfig['wrapper_offset_left'];
			$this->config['wrapper_offset_top'] = $zoomConfig['wrapper_offset_top'];
			$this->config['wrapper_width'] = $zoomConfig['wrapper_width'];
			$this->config['wrapper_height'] = $zoomConfig['wrapper_height'];
	    	$this->config['upscale_image_width'] = $zoomConfig['zoom_ratio'] * $zoomConfig['image_width'];
	    	$this->config['upscale_image_height'] = $zoomConfig['zoom_ratio'] * $zoomConfig['image_height'];
		}
	}
	
	public function productListConfig(){
		return $this->config;
	}
	
	public function showSwatches($_product, $_colorswatch){
		if (Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $_product->getTypeId()) {
			$attributeCollection = $_product->getTypeInstance(true)->getConfigurableAttributes($_product);

			$_attribute = $attributeCollection->getFirstItem();
//			foreach ($attributeCollection as $_attribute) { // to do in next verion to support more attributes.
				echo "<ul class=\"colorswatch-attribute colorswatch-attribute-list-{$_attribute->getAttributeId()}-{$_product->getId()}\">";
				$swatchCounter = 0;
				foreach ($_attribute->getSwatches() as $swatch) { 
					if ($this->config['max_swatches'] <= $swatchCounter) {
						break;
					}

					if ($swatch->getInStock()) {
						echo "<li class=\"colorswatch-{$_attribute->getAttributeId()}-{$swatch->getOptionId()} colorswatch-swatch-container\" >";
						echo "<span class=\"swatch\" style=\"width:30px;height:30px\" >";
						$swatchImages = Mage::helper('colorswatch/images')->init($swatch);
						if ($swatch->getImageBase()) {
							echo "<img class=\"{$swatchImages->getClassName()}\" src=\"{$swatchImages->resize(30, 30)}\" />";
						} else {
							echo $swatch->getStoreLabel();
						}
						echo "</span></li>";
						$swatchCounter++;
					}

				}
				echo "</ul>";
				echo "<input type=\"hidden\" id=\"hidden-attribute-{$_product->getId()}-{$_attribute->getAttributeId()}\" name=\"super_attribute[{$_product->getId()}][{$_attribute->getAttributeId()}]\" class=\"required-entry hidden-super-attribute-select\" />";
				$attributeCounter++;
//			}
		}
	}

	public function showSwatches22($_product,$_colorswatch){
		$swatchImageWidth = $this->config['swatch_image_width'];
		$swatchImageHeight = $this->config['swatch_image_height'];
		$maxSwatches = $this->config['max_swatches'];
		
		if ($_product->getData('smd_colorswatch_product_list')){
			$attributes = $_colorswatch->setProduct($_product)->getAttributes();
			$attribute = null;
			foreach ($_colorswatch->setProduct($_product)->getAttributes() as $att) {
				if ($attribute == null) {
					$_attributeId = $att->getAttributeId();
					if (Mage::getModel('colorswatch/attribute_settings')->getConfig($_attributeId, 'enable_colorswatch')){
						$attribute = $att;
					}
				}
			}
			if ($attribute == null) {
				$attribute = $att;
			}
			$_colorswatch->getColorSwatchArray();
			$_attributeId = $attribute->getAttributeId();
			$htmlOutput = "";
			$swatchCss ="";
			$scriptOutput ="";
			$zoomOutput = "";
			if (Mage::getModel('colorswatch/attribute_settings')->getConfig($_attributeId, 'enable_colorswatch')) {
				$htmlOutput .= "<ul class=\"colorswatch-attribute colorswatch-attribute-list-".$_attributeId."-".$_product->getId()."\">"."\n";
				$swatchCounter = 0;
				$swatchCss.="<style type=\"text/css\">"."\n";
				foreach ($attribute->getColorswatchOptions() as $_option) {
					$swatchCounter++;
					if ($swatchCounter > $maxSwatches) {
						continue;
					}
					$_optionId = $_option->getData('option_id');
					$swatch = $_colorswatch->getSwatch($_attributeId, $_optionId);
					if (is_object($swatch) && !$swatch->getIsDisabled()) {
						$htmlOutput .= "<li class=\"colorswatch-".$_attributeId."-".$_optionId." colorswatch-swatch-container ".($swatch->getIsDisabled() ? 'not_allowed not_clickable' : '')."\">"."\n";
						
						if ($swatch->getImage()->getSwatchImage()->isImageExsist()) {
							$htmlOutput .= "<span class=\"swatch\" style=\"width:".$swatchImageWidth."px;height:".$swatchImageHeight."px\" >".$_option->getData('value')."</span>"."\n";
						} else {	
							$htmlOutput .= "<span class=\"swatch\" style=\"width:".$swatchImageWidth."px;height:".$swatchImageHeight."px\" >".$_option->getData('value')."</span>"."\n";
						}
						$htmlOutput .= "<span class=\"status\" >&nbsp;</span>"."\n";
						$htmlOutput .= "</li>"."\n";
						
						if ($swatch->getImage()->getSwatchImage()->isImageExsist()) {
							$swatchCss.=".colorswatch-". $_attributeId."-". $_optionId." span.swatch { background: url('". $swatch->getImage()->getSwatchImage()->resize($swatchImageWidth, $swatchImageHeight)."') no-repeat 0 0; text-indent: -9999px; }"."\n";
						}
						if ($swatch->getImage()->getHoverImage()->isImageExsist()) {
							$swatchCss.=".colorswatch-". $_attributeId."-". $_optionId." span.swatch:hover { background: url('". $swatch->getImage()->getHoverImage()->resize($swatchImageWidth, $swatchImageHeight)."') no-repeat 0 0; text-indent: -9999px; }"."\n";
						}
						if ($swatch->getImage()->getActiveImage()->isImageExsist()) {
							$swatchCss.=".colorswatch-". $_attributeId."-". $_optionId.".active span.swatch { background: url('". $swatch->getImage()->getActiveImage()->resize($swatchImageWidth, $swatchImageHeight)."') no-repeat 0 0; text-indent: -9999px; }"."\n";
						}
						if ($swatch->getImage()->getDisabledImage()->isImageExsist()) {
							$swatchCss.=".colorswatch-". $_attributeId."-". $_optionId.".not_allowed span { background: url('". $swatch->getImage()->getDisabledImage()->resize($swatchImageWidth, $swatchImageHeight)."') no-repeat 0 0; text-indent: -9999px; }"."\n";
							$swatchCss.=".colorswatch-". $_attributeId."-". $_optionId.".not_clickable span { background: url('". $swatch->getImage()->getDisabledImage()->resize($swatchImageWidth, $swatchImageHeight)."') no-repeat 0 0; text-indent: -9999px; }"."\n";
						}
						
					} else { 
						$swatchCounter--; 
					}  
				}
				$htmlOutput .= "</ul>"."\n";
				$swatchCss.="</style>"."\n";
				if ($swatchCounter > $maxSwatches) {
					$htmlOutput .= "<a class=\"more-swatches-available-".$_product->getId()."\" href=\"".$_product->getProductUrl()."\">More ".$attribute->getProductAttribute()->getFrontendLabel() ."`s are available...</a>";
				}
				$htmlOutput .= "<br class=\"clear\" style=\"clear:both;\"/>"."\n";
				
				$scriptOutput .="<script type=\"text/javascript\">"."\n";
				$scriptOutput .="document.observe(\"dom:loaded\", function() {"."\n";
				$scriptOutput .="	new SMDesignColorswatch('.colorswatch-attribute-list-".$_attributeId."-".$_product->getId()." li', new ColorswatchConfig(".$_colorswatch->getJsonConfig()."), {"."\n";
				$scriptOutput .="		'mainImageSelector' : '.item-".$_product->getId()." .product-image img',"."\n";
				$scriptOutput .="		'image_url' : '".Mage::getUrl('colorswatchproductlist/get/image')."'"."\n";
				$scriptOutput .="	} );"."\n";
				$scriptOutput .="});"."\n";
				$scriptOutput .="</script>"."\n";
				
				echo $htmlOutput;
				echo $swatchCss;
				echo $scriptOutput;
				echo $this->generateZoomScript($_product);
			}
		}
	}
	
	public function generateZoomScript($_product){
		$zoomOutput = "";
		if ($this->config['use_zoom']) {
			$zoomOutput .="<script type=\"text/javascript\">"."\n";
			$zoomOutput .="Event.observe(window, 'load', function() {"."\n";
			$zoomOutput .="	new SMDZoom('image-".$_product->getId()."', {"."\n";
			$zoomOutput .="		useParentNode: true,"."\n";
			$zoomOutput .="		useRel: true,"."\n";
			
			$zoomOutput .="		zoomRatio: ".$this->config['zoom_ratio'].","."\n";
			$zoomOutput .="		errorReport: ".($this->config['show_info_error'] ? 'true' : 'false').","."\n";
			$zoomOutput .="		width: ".$this->config['wrapper_width'].","."\n";
			$zoomOutput .="		height: ".$this->config['wrapper_height'].","."\n";
			$zoomOutput .="		offsetLeft: ".$this->config['wrapper_offset_left'].","."\n";
			$zoomOutput .="		offsetTop: ".$this->config['wrapper_offset_top'].","."\n";
			
			$zoomOutput .="		onclick: function(zoomClass) { },"."\n";
			$zoomOutput .="		dblclick: function(zoomClass) {  },"."\n";
			$zoomOutput .="		insideZoom: ".($this->config['zoom_type'] != 0 ? 'true' : 'false').","."\n";
			$zoomOutput .="		insideZoomFull: ".($this->config['zoom_type'] ? 'true' : 'false')."\n";
			$zoomOutput .="	});"."\n";
			$zoomOutput .="});"."\n";
			$zoomOutput .="</script>"."\n";
		}
		return $zoomOutput;
	}
	
}
<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Conf
*/
class Amasty_Conf_Block_Catalog_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    protected $_optionProducts;
    
    protected function _afterToHtml($html)
    {
        $attributeIdsWithImages = Mage::registry('amconf_images_attrids');
        $list = (Mage::registry('isList')) ? 1 : 0;
        $html = parent::_afterToHtml($html);
        if ('product.info.options.configurable' == $this->getNameInLayout())
        {
            if (Mage::getStoreConfig('amconf/general/hide_dropdowns') || $list)
            {
                if (!empty($attributeIdsWithImages))
                {
                    foreach ($attributeIdsWithImages as $attrIdToHide)
                    {
                        $html = preg_replace('@(id="attribute' . $attrIdToHide . ')(-)?([0-9]*)(")(\s+)(class=")(.*?)(super-attribute-select)(-)?([0-9]*)@', '$1$2$3$4$5$6$7$8$9$10 no-display', $html);
                    }
                }
            }
            if (Mage::getStoreConfig('amconf/general/show_clear')&& !$list)
            {
                $html = '<a href="#" onclick="javascript: spConfig.clearConfig(); return false;">' . $this->__('Reset Configuration') . '</a>' . $html;
            }
            if (!$list){
                 $html = '<script type="text/javascript">var showAttributeTitle =' . intval(Mage::getStoreConfig('amconf/general/show_attribute_title'))  . '</script>'
                         . '<script type="text/javascript" src="' . Mage::getBaseUrl('js') . 'amasty/amconf/configurable.js"></script>'               
                         . $html;

            }
            $simpleProducts = $this->getProduct()->getTypeInstance(true)->getUsedProducts(null, $this->getProduct());
            if ($this->_optionProducts)
            {
                $noimgUrl = Mage::helper('amconf')->getNoimgImgUrl();
                $this->_optionProducts = array_values($this->_optionProducts);
                foreach ($simpleProducts as $simple)
                {
                    $key = array();
                    for ($i = 0; $i < count($this->_optionProducts); $i++)
                    {
                        foreach ($this->_optionProducts[$i] as $optionId => $productIds)
                        {
                            if (in_array($simple->getId(), $productIds))
                            {
                                $key[] = $optionId;
                            }
                        }
                    }
                    if ($key)
                    {
                        $strKey = implode(',', $key);
                        // @todo check settings:
                        // array key here is a combination of choosen options
                        $confData[$strKey] = array(
                            'short_description' => $simple->getShortDescription(),
                            'description'       => $simple->getDescription(),
                        );
                        $parentProduct = Mage::getModel('catalog/product')->load($simple->getParentId());
                        if($parentProduct){
                              $confData[$strKey]['parent_image'] =(string)($this->helper('catalog/image')->init($parentProduct, 'small_image')->resize(135)); 
                              if(!('no_selection' == $simple->getSmallImage() || '' == $simple->getSmallImage())){
                                   $confData[$strKey]['small_image'] = (string)($this->helper('catalog/image')->init($simple, 'small_image')->resize(135));
                              }
                              else{
                                   $confData[$strKey]['small_image'] = (string)($this->helper('catalog/image')->init($parentProduct, 'small_image')->resize(135));
                              }
                        }     
                        if (Mage::getStoreConfig('amconf/general/reload_name'))
                        {
                            $confData[$strKey]['name'] = $simple->getName();
                        }
                        $confData[$strKey]['price'] = $simple->getPrice();
                        if ($simple->getImage() && Mage::getStoreConfig('amconf/general/reload_images'))
                        {
                            $confData[$strKey]['media_url'] = $this->getUrl('amconf/media', array('id' => $simple->getId())); // media_url should only exist if we need to re-load images
                        } elseif ($noimgUrl) 
                        {
                            $confData[$strKey]['noimg_url'] = $noimgUrl;
                        }
                        //for >3
                        if(Mage::getStoreConfig('amconf/general/oneselect_reload')){
                            $pos = strpos($strKey, ",");
                            if($pos){
                                $pos = strpos($strKey, ",", $pos+1);
                                if($pos){
                                    $newKey = substr($strKey, 0, $pos);
                                    $confData[$newKey] =  $confData[$strKey];   
                                }
                            }
                            
                        }
                        
                    }
                }
                if ($list){
                    $html .= '<script type="text/javascript"> confData['.$this->getProduct()->getEntityId().'] = new AmConfigurableData(' . Zend_Json::encode($confData) . ');
                                                              confData['.$this->getProduct()->getEntityId().'].textNotAvailable = "' . $this->__('Choose previous option please...') . '";
                                                              confData['.$this->getProduct()->getEntityId().'].mediaUrlMain = "' . $this->getUrl('amconf/media', array('id' => $this->getProduct()->getId())) . '";
                                                              confData['.$this->getProduct()->getEntityId().'].oneAttributeReload = "' . (boolean) Mage::getStoreConfig('amconf/general/oneselect_reload') . '";
								amRequaredField = "' .  $this->__('&uarr;  This is a required field.') . '";
                    </script>';
                }
                else {
                    $html .= '<script type="text/javascript"> confData = new AmConfigurableData(' . Zend_Json::encode($confData) . ');
                                                              confData.textNotAvailable = "' . $this->__('Choose previous option please...') . '";
                                                              confData.mediaUrlMain = "' . $this->getUrl('amconf/media', array('id' => $this->getProduct()->getId())) . '";
                                                              confData.oneAttributeReload = "' . (boolean) Mage::getStoreConfig('amconf/general/oneselect_reload') . '";
                    </script>';
                }
                
                if (Mage::getStoreConfig('amconf/general/hide_dropdowns'))
                {
                    $html .= '<script type="text/javascript">Event.observe(window, \'load\', spConfig.processEmpty);</script>';
                }
                if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Lbox/active'))
                {
                    $tmp = $list? '['.$this->getProduct()->getEntityId().']' : '';
                    $html .= '<script type="text/javascript">confData'.$tmp.'.amlboxInstalled = true;</script>';
                }
            }
        }
        
        return $html;
    }
    
    protected function getImagesFromProductsAttributes(){
        $collection = Mage::getModel('amconf/product_attribute')->getCollection();
        
        $collection->getSelect()->join( array(
            'prodcut_super_attr' => $collection->getTable('catalog/product_super_attribute')),
                'main_table.product_super_attribute_id = prodcut_super_attr.product_super_attribute_id', 
                array('prodcut_super_attr.attribute_id')
            );
        
        $collection->addFieldToFilter('prodcut_super_attr.product_id', $this->getProduct()->getEntityId());
        $collection->addFieldToFilter('use_image_from_product', 1);
        
        $attributes = $collection->getItems();

        $ret = array();
        
        foreach($attributes as $attribute){
            $ret[] = $attribute->getAttributeId();
        }
        
        return $ret;
    }
    
    public function getJsonConfig()
    {
        
        $attributeIdsWithImages = array();
        $jsonConfig = parent::getJsonConfig();
        $config = Zend_Json::decode($jsonConfig);
        $productImagesAttributes = $this->getImagesFromProductsAttributes();
      
        if (Mage::helper('amconf')->getOptionsImageSize()){
             $config['size'] = Mage::helper('amconf')->getOptionsImageSize();
        }
        foreach ($config['attributes'] as $attributeId => $attribute)
        {
            if (Mage::getModel('amconf/attribute')->load($attributeId, 'attribute_id')->getUseImage())
            {
                $attributeIdsWithImages[] = $attributeId;
                $config['attributes'][$attributeId]['use_image'] = 1;
            }
            else{
                 if(Mage::registry('isList')){
                      $attributeIdsWithImages[] = $attributeId;
                 }
            }
            foreach ($attribute['options'] as $i => $option)
            {
                $this->_optionProducts[$attributeId][$option['id']] = $option['products'];
                if (in_array($attributeId, $productImagesAttributes)){
                    
                    foreach($option['products'] as $product_id){
//                        
                        $size = Mage::getStoreConfig('amconf/product_image_size/thumb');
                        $product = Mage::getModel('catalog/product')->load($product_id);
                        $config['attributes'][$attributeId]['options'][$i]['image'] = 
                            (string)Mage::helper('catalog/image')->init($product, 'image')->resize($size);
                        break;
                    }
                }
                else if (Mage::getModel('amconf/attribute')->load($attributeId, 'attribute_id')->getUseImage())
                {
                    $config['attributes'][$attributeId]['options'][$i]['image'] = Mage::helper('amconf')->getImageUrl($option['id']);
                }
            }
        }
        Mage::unregister('amconf_images_attrids');
        Mage::register('amconf_images_attrids', $attributeIdsWithImages, true);

        return Zend_Json::encode($config);
    }
    
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($this->hasCustomAddToCartUrl()) {
            return $this->getCustomAddToCartUrl();
        }
        if ($this->getRequest()->getParam('wishlist_next')){
            $additional['wishlist_next'] = 1;
        }
        $addUrlKey = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $addUrlValue = Mage::getUrl('*/*/*', array('_use_rewrite' => true, '_current' => true));
        $additional[$addUrlKey] = Mage::helper('core')->urlEncode($addUrlValue);
        return $this->helper('checkout/cart')->getAddUrl($product, $additional);
    }
}
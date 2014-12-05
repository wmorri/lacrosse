<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Conf
*/
class Amasty_Conf_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $onClick;
    
    protected $amConf;
    
    public function getImageUrl($optionId)
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 
                                                    'amconf' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        if (file_exists($uploadDir . $optionId . '.jpg'))
        {
            return Mage::getBaseUrl('media') . 'amconf' . '/' . 'images' . '/' . $optionId . '.jpg';
        }
        return '';
    }
    
    public function getNoimgImgUrl()
    {
        if (Mage::getStoreConfig('amconf/general/noimage_img'))
        {
            return Mage::getBaseUrl('media') . 'amconf/noimg/' . Mage::getStoreConfig('amconf/general/noimage_img');
        }
        return '';
    }
    
    public function getOptionsImageSize()
    {
            return Mage::getStoreConfig('amconf/general/listimg_size');
    } 
    
    public function getClickUrl()
    {
            return $this->onClick;
    }  
    
    public function getAmconfAttr()
    {
            return $this->amConf;
    } 
    
    public function getHtmlBlock($_product)
    {
        $html = '';
        $this->onClick = "setLocation('".Mage::app()->getLayout()->createBlock('catalog/product_list', 'catalog.catalog_product_list')->getAddToCartUrl($_product)."')";
        if($_product->isConfigurable()){
                     $html .= '<div id="insert" style="display: none;"></div>';
             $block = Mage::app()->getLayout()->createBlock('catalog/product_view_type_configurable', 'catalog.product_view_type_configurable',array('template'=>"catalog/product/view/type/default.phtml"));
             $block->setProduct($_product);
             $html .= '<script type="text/javascript"> var spConfig = new Product.Config('.$block->getJsonConfig().'); </script>'; 
             $imageBlock = Mage::app()->getLayout()->createBlock('amconf/catalog_product_list_images', 'amconf.catalog_product_list_images');
             $html .= $imageBlock->show($_product->getEntityId());
             $blockForForm = Mage::app()->getLayout()->createBlock('amconf/catalog_product_view_type_configurable', 'amconf.catalog_product_view_type_configurable');
             $blockForForm->setProduct($_product); 
             $this->onClick = "formSubmit(this,'".$blockForForm->getSubmitUrl($_product)."', '".$_product->getId()."', ".$imageBlock->getAttributes().")";
             $this->amConf = "createForm('".$blockForForm->getSubmitUrl($_product)."', '".$_product->getId()."', ".$imageBlock->getAttributes().")";
        }
        return $html;
   }
}
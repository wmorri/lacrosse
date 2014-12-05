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

class EcommerceTeam_Sln_Adminhtml_Option_ImageController
    extends Mage_Adminhtml_Controller_Action
{
    public function uploadAction()
    {
        try {
            /** @var $config Mage_Catalog_Model_Product_Media_Config */
            $config = Mage::getSingleton('catalog/product_media_config');

            $uploader = new Varien_File_Uploader('option_image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));

            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            $result = $uploader->save($config->getBaseTmpMediaPath());

            $result['url'] = $config->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Mage_Core_Exception $e) {
            $result = array('error' => $e->getMessage(), 'errorcode'=>$e->getCode());
        } catch (Exception $e) {
            $result = array('error' => Mage::helper('core')->__('Unknown Error.'));
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}

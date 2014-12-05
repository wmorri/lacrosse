<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category 	Magestore
 * @package 	Magestore_Storelocator
 * @copyright 	Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license 	http://www.magestore.com/license-agreement.html
 */

 /**
 * Storelocator Adminhtml Controller
 * 
 * @category 	Magestore
 * @package 	Magestore_Storelocator
 * @author  	Magestore Developer
 */
class Magestore_Storelocator_Adminhtml_StorelocatorController extends Mage_Adminhtml_Controller_Action
{
        const ZoomLevel = 12;
	/**
	 * init layout and set active for current menu
	 *
	 * @return Magestore_Storelocator_Adminhtml_StorelocatorController
	 */
	protected function _initAction(){
		$this->loadLayout()
			->_setActiveMenu('storelocator/storelocator')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		return $this;
	}
 
	/**
	 * index action
	 */
	public function indexAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){return;}
		$this->_initAction()
			->renderLayout();
	}

	/**
	 * view and edit item action
	 */
	public function editAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){return;}
		
		$id	 = $this->getRequest()->getParam('id');
               
                $store     = $this->getRequest()->getParam('store');   
                 
		$model  = Mage::getModel('storelocator/storelocator')->setStoreId($store)->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data))
				$model->setData($data);

			Mage::register('storelocator_data', $model);
                        
			$this->loadLayout();
			$this->_setActiveMenu('storelocator/storelocator');
                        
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
                        
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('storelocator/adminhtml_storelocator_edit'))
				->_addLeft($this->getLayout()->createBlock('storelocator/adminhtml_storelocator_edit_tabs'));
                        
			$this->renderLayout();
                        
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('storelocator')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	/**
	 * save item action
	 */
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {                   
                    $id = $this->getRequest()->getParam('id');
                    $model = Mage::getModel('storelocator/storelocator');
                    $store = $this->getRequest()->getParam('store');
                    //set statevalue
//                    Zend_debug::dump($data);die();
                    if(isset($data['state_id'])){
                        $state = Mage::getModel('directory/region')->load($data['state_id']);
                        $data['state'] = $state->getName();
                    }
                    
                    if(!$data['zoom_level']){
                        $data['zoom_level'] = self::ZoomLevel;
                    }                    
                    if(isset($data['zoom_level_value']) && $data['zoom_level_value']){
                        $data['zoom_level'] = intval($data['zoom_level_value']);    
                    }
                     $deleteIcon = 0;
                    
                     if(isset($data['image_icon'])){
                         if(isset($data['image_icon']['delete'])){
                             $deleteIcon = 1;
                             $data['image_icon'] = '';
                         }else{
                             $imageData = explode('/',$data['image_icon']['value']);
                             
                             $data['image_icon'] = $imageData['4'];                             
                         }                         
                     }
                     if(isset($_FILES['image_icon']) && $_FILES['image_icon']['name']){
                         $data['image_icon'] = $_FILES['image_icon']['name'];                        
                         
                     }
                     
                     $model->setData($data)
                            ->setStoreId($store)
                            ->setData('storelocator_id',$id);
                    try {
                        $model->save();                        
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('storelocator')->__('Store was successfully saved'));
			Mage::getSingleton('adminhtml/session')->setFormData(false);
                        
                        if ($id == null) {
                            if (!isset($data['radio'])) {
                                $data['radio'] = 1;
                            }
                            if (isset($data['images_id'])) {
                                Mage::helper('storelocator')->saveImageStore($data['images_id'], $model->getCollection()->getLastItem()->getId(), $_FILES, $data['radio']);
                            }
                            if (isset($_FILES['image_icon']) && $_FILES['image_icon']['name']) {
                                Mage::helper('storelocator')->saveIcon($_FILES['image_icon'], $model->getCollection()->getLastItem()->getId());
                            }
                        } else {
                            if (!isset($data['radio'])) {
                                $data['radio'] = 1;
                            }
                            if (isset($data['images_id'])) {
                                Mage::helper('storelocator')->saveImageStore($data['images_id'], $id, $_FILES, $data['radio']);
                            }
                            if (isset($_FILES['image_icon']) && $_FILES['image_icon']['name']) {
                                Mage::helper('storelocator')->saveIcon($_FILES['image_icon'], $id);
                            }
                        }
                        
                        if(isset($data['tags_store'])){
                            $tag = explode(",", $data['tags_store']);
                            $tags = array();
                            foreach ($tag as $item){
                                $itemTag = trim($item);
                                if ($itemTag) $tags[] = $itemTag;
                            }                        
                            Mage::helper('storelocator')->saveTagToStore($tags, $model->getId());
                        }
                    
                        if ($deleteIcon) {
                            Mage::helper('storelocator')->deleteImageIcon($model->getId(), $data['image_icon']);
                        }

                        if ($this->getRequest()->getParam('back')) {
                            $this->_redirect('*/*/edit', array('id' => $model->getId()));
                            return;
                        }
                        $this->_redirect('*/*/');
                        return;
                    } catch (Exception $e) {
                         Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                         Mage::getSingleton('adminhtml/session')->setFormData($data);
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                         return;
                    }
                			
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('storelocator')->__('Unable to find store to save'));
		$this->_redirect('*/*/');
	}
 
	/**
	 * delete item action
	 */
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('storelocator/storelocator');
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	/**
	 * mass delete item(s) action
	 */
	public function massDeleteAction() {
		$storelocatorIds = $this->getRequest()->getParam('storelocator');
		if(!is_array($storelocatorIds)){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
		}else{
			try {
				foreach ($storelocatorIds as $storelocatorId) {
					$storelocator = Mage::getModel('storelocator/storelocator')->load($storelocatorId);
					$storelocator->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($storelocatorIds)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}
	
	/**
	 * mass change status for item(s) action
	 */
	public function massStatusAction() {
		$storelocatorIds = $this->getRequest()->getParam('storelocator');
		if(!is_array($storelocatorIds)) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
		} else {
			try {
				foreach ($storelocatorIds as $storelocatorId) {
					$storelocator = Mage::getSingleton('storelocator/storelocator')
						->load($storelocatorId)
						->setStatus($this->getRequest()->getParam('status'))
						->setIsMassupdate(true)
						->save();
				}
				$this->_getSession()->addSuccess(
					$this->__('Total of %d record(s) were successfully updated', count($storelocatorIds))
				);
			} catch (Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	/**
	 * export grid item to CSV type
	 */
	 public function exportCsvAction() {
        $fileName = 'storelocator.csv';
        $content = Mage::getModel('storelocator/exporter')
                ->exportStoreLocator();

        $this->_sendUploadResponse($fileName, $content);
    }

	/**
	 * export grid item to XML type
	 */
	public function exportXmlAction() {
        $fileName = 'storelocator.xml';
        $content = Mage::getModel('storelocator/exporter')
                ->getXmlStoreLocator();

        $this->_sendUploadResponse($fileName, $content);
    }
	
	protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('storelocator');
	}   
}
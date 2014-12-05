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
 * Storelocator Index Controller
 * 
 * @category 	Magestore
 * @package 	Magestore_Storelocator
 * @author  	Magestore Developer
 */
class Magestore_Storelocator_IndexController extends Mage_Core_Controller_Front_Action
{
	/**
	 * index action
	 */
	public function indexAction(){
            //if(Mage::helper('storelocator')->getConfig('enable')){
		$this->loadLayout();
                $this->getLayout()->getBlock('head')->setTitle(Mage::helper('storelocator')->getConfig('page_title')); 
		$this->renderLayout();
            //}
	}
        
        public function viewAction()
        {
            //renderlayout view detail store
            //if(Mage::helper('storelocator')->getConfig('enable')){
                $this->loadLayout();
                $this->getLayout()->getBlock('head')->setTitle(Mage::helper('storelocator')->getConfig('page_title')); 
                $this->renderLayout();
            //}
        }
        
        public function preDispatch()
        {
            if(!Mage::helper('storelocator')->getConfig('enable')){			
                 header('Location: '.Mage::getUrl());          
                 exit;      				
            }else{                
                return $this;
            }
        }
}

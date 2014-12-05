<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ jsIdiYMtQtahrImf('29617f7af2f3572d0646cdc94c8cca8b');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_UnsubscribeController extends Mage_Core_Controller_Front_Action
{
    public function cartAction()
    {
        $code = (string) $this->getRequest()->getParam('code');
        $id   = (int) $this->getRequest()->getParam('id');
        
        $history = Mage::getModel('adjcartalert/history')->load($id);
        if (!$history->getId() || $history->getRecoverCode() != $code){
            $this->_redirect('/');
            return;
        }
        
        $unsubscribe = Mage::getModel('adjcartalert/unsubscribe');
        if( $unsubscribe->deleteAllMode() ) {
            $unsubscribe->deletePending( $history->getCustomerEmail() );
        } elseif( $unsubscribe->stopListMode() ) {
            $unsubscribe->deletePending( $history->getCustomerEmail() )
                ->addToStopList( $history->getCustomerEmail(), Mage::app()->getStore()->getGroup()->getId() );
        } elseif( $unsubscribe->allStoresMode() ) {
            $unsubscribe->deletePending( $history->getCustomerEmail() )
                ->addToStopList( $history->getCustomerEmail() );            
        } else {
            Mage::register('adjcartalert_history', $history);
            if($this->getRequest()->getPost('confirmed') == 1) {
                $unsubscribe->deletePending( $history->getCustomerEmail() );
                $history->setConfirmed(true);
            }
            //customer pending action
        }
        //code and cart are validated, unsubscribe user from alerts
        
        // customer. login

        $this->loadLayout();
        $this->renderLayout();
    }
    
} } 
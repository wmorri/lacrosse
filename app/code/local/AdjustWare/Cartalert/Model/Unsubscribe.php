<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ escVwSmdRdZqacyN('f2b5df4f3a04b1d1895711038e5ab10d');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Model_Unsubscribe extends Mage_Core_Model_Abstract
{
    protected $_unsubscribeConfig = null;
    
    public function getUnsubscribeConfig()
    {
        if(is_null($this->_unsubscribeConfig)) {
            $this->_unsubscribeConfig = Mage::getStoreConfig('catalog/adjcartalert/unsubscribe');
        }
        return $this->_unsubscribeConfig;
    }
    
    public function deleteAllMode()
    {
        if($this->getUnsubscribeConfig() == 0) {
            return true;
        }
        return false;
    }
    
    public function stopListMode()
    {
        if($this->getUnsubscribeConfig() == 1) {
            return true;
        }
        return false;        
    }
    
    public function allStoresMode()
    {
        if($this->getUnsubscribeConfig() == 2) {
            return true;
        }
        return false;        
    }
    
    public function clientMode()
    {
        if($this->getUnsubscribeConfig() == 3) {
            return true;
        }
        return false;        
    }
    
    /**
    * Delete all cartalerts by email
    * 
    * @param string $email
    */
    public function deletePending( $email )
    {
        $cartalert = Mage::getResourceModel('adjcartalert/cartalert')
            ->cancelAlertsFor( $email );
        return $this;
    }
    
    /**
    * add customer email to stop list to prevent sending alerts again
    * 
    * @param string $email
    * @param int $group_id
    */
    public function addToStopList( $email, $group_id = 0 ) {
        $stoplist = Mage::getModel('adjcartalert/stoplist');
        if( $stoplist->contains($group_id, $email) ) {
            //email already in stoplist
            return $this;
        }
        $stoplist->setCustomerEmail($email)
            ->setStoreId($group_id)
            ->setDate(now())
            ->save();
        return $this;
    }
} } 
<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
 /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 *****************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
 /***************************************
 *         DISCLAIMER   *
 *****************************************/
 /* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 *****************************************************
 * @category   Belvg
 * @package    Belvg_Easyauth
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */?>
<?php

class Belvg_Easyauth_Model_Main extends Mage_Core_Model_Abstract
{  
    
    public function _construct()
    {
        parent::_construct();        
    }

    public function getSettings(){
	
	$oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
	$result = $oDb->query("SELECT * FROM ".$this->_table);	
	$arr = array();
	while ($row = $result->fetch(PDO::FETCH_ASSOC)){			
		$row['pages'] = explode(",",$row['pages']);	   
		$arr[] = $row;
	}	
	return $arr;
    }

     public function saveSettings($aDBInfo){	
	$aDBInfo['pages'] = implode(",",$aDBInfo['pages']);
	$oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
	if (Mage::app()->getRequest()->getParam('id'))
	  $resu	= $oDb->update($this->_table,$aDBInfo,array('twitter_id = '.Mage::app()->getRequest()->getParam('id')));
	else $oDb->insert($this->_table,$aDBInfo);
	return $resu;
    }


    public function checkExist($email){        
		$_customer = Mage::getModel('customer/customer')->setStore(Mage::app()->getStore())->loadByEmail($email);		
		return $_customer;
    }

    public function addTwitterAccount($uid,$tid){
        $_table = 'belvg_twitter_users';
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        $aDBInfo = array(
            'twitter_id' => $tid,
            'user_id' => $uid,
        );
	$oDb->insert($_table,$aDBInfo);
    }


    public function getTwitterRel($id){
         $_table = 'belvg_twitter_users';
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
	$result = $oDb->query("SELECT * FROM ".$_table." WHERE twitter_id = ".$id);
	$return = array();
	while ($row = $result->fetch(PDO::FETCH_ASSOC)){
            $return = $row;
	}
	return $return;
    }


  
}
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
 * Storelocator Model
 * 
 * @category 	Magestore
 * @package 	Magestore_Storelocator
 * @author  	Magestore Developer
 */
class Magestore_Storelocator_Model_Gmap extends Varien_Object
{
    const GEOURL = 'http://maps.googleapis.com/maps/api/geocode/json?address=';
    const GPARAM = "&sensor=true&key=";
    const FOMAT_ADDRESS = "{{street}},{{city}}&{{region}}&{{country}}|{{zipcode}}";
    /**
     * 
     * @param type $address
     * return lat
     */
    public function getCoordinates($address){
        $address = $address ? $address : $this->getAddress();
		
        $this->setAddress($address);
		
	if(! $address)
            return;
		
	$address = $this->getFormualAddress();
       
        $url = self::GEOURL;
        $url .= $address;
        $url .= self::GPARAM;
        $url .= $this->getGoogleAPI();     		
        try{
            $result = Mage::helper('storelocator')->getResponseBody($url);      
            $result =  Zend_Json_Decoder::decode($result);       	         
            if($result['status'] != 'OK') return null ;
            else{            
                return $result['results']['0']['geometry']['location'];            
            }                
        }catch(Exception $e){
            
        }        
    }
    /**
     * return formual address
     */
    public function getFormualAddress(){
        $address = $this->getAddress();
		
        $formatedaddress = self::FOMAT_ADDRESS;
		$formatedaddress = str_replace('{{street}}',$address['street'],$formatedaddress);
        $formatedaddress = str_replace('{{city}}',$address['city'],$formatedaddress);
		$formatedaddress = str_replace('{{region}}','region='.$address['region'],$formatedaddress);		
		$formatedaddress = str_replace('{{country}}','components=country:'.$address['country'],$formatedaddress);
		$formatedaddress = str_replace('{{zipcode}}','postal_code_prefix:'.$address['zipcode'],$formatedaddress);
		
		$formatedaddress = str_replace(' ','+',$formatedaddress);

	return $formatedaddress;
    }
    /**
     * return google api
     */
    public function getGoogleAPI(){
        
        return Mage::helper('storelocator')->getConfig('gkey');
    }
}
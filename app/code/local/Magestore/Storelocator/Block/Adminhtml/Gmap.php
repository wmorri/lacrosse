<?php
class Magestore_Storelocator_Block_Adminhtml_Gmap extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface {
	
        
    protected $_element;
	
    /**
     * constructor
    */
    public function __construct(){

            $this->setTemplate('storelocator/gmap.phtml');
    }

    /*
     * renderer
    */
    public function render(Varien_Data_Form_Element_Abstract $element){
            $this->setElement($element);
            return $this->toHtml();
    }

    /**
     * get and set element
    */
    public function setElement(Varien_Data_Form_Element_Abstract $element){
            $this->_element = $element;
            return $this;
    }
    public function getElement(){
            return $this->_element;
    }
        

    public function getStore()
    {
        $id = $this->getRequest()->getParam('id');
        if(!$id) return null;
        $store = Mage::getModel('storelocator/storelocator')->load($id);
        return $store;
    }

    public function getCoodinates()
    {
       $store = $this->getStore();
       if($store){
            $address['street'] = $store->getAddress();
            $address['city'] = $store->getCity();
            $address['region'] = $store->getRegion();
            $address['zipcode'] = $store->getState();
            $address['country'] = $store->getCountry();		
            $coordinates = Mage::getModel('storelocator/gmap')
                    ->getCoordinates($address);               
            
            return $coordinates;
       }else{
           return null;
       }
    }	
}
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
class Magestore_Storelocator_Model_Storelocator extends Mage_Core_Model_Abstract {

    protected $_store_id = null;

    public function _construct() {
        parent::_construct();
        if ($storeId = Mage::app()->getStore()->getId()) {
            $this->setStoreId($storeId);
        }
        $this->_init('storelocator/storelocator');
    }

    // Load StoreId (multi store) to set data multi store
    protected function _beforeSave() {
        if ($storeId = $this->getStoreId()) {
            $defaultStore = Mage::getModel('storelocator/storelocator')->load($this->getId());
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_default')) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                $this->setData($attribute, $defaultStore->getData($attribute));
            }
        }
        return parent::_beforeSave();
    }

    protected function _afterSave() {
        if ($storeId = $this->getStoreId()) {
            $storeAttributes = $this->getStoreAttributes();

            foreach ($storeAttributes as $attribute) {
                $attributeValue = Mage::getModel('storelocator/storevalue')
                        ->loadAttributeValue($this->getId(), $storeId, $attribute);
                if ($this->getData($attribute . '_in_store')) {
                    try {
                        $attributeValue->setValue($this->getData($attribute . '_value'))->save();
                    } catch (Exception $e) {
                        
                    }
                } elseif ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
        return parent::_afterSave();
    }

    public function getStoreId() {
        return $this->_store_id;
    }

    public function setStoreId($id) {
        $this->_store_id = $id;
        return $this;
    }

    public function getStoreAttributes() {
        return array(
            'name',
            'status',
            'sort',
            'description',
            'address',
            'city',
        );
    }

    //info multistore
    public function load($id, $field = null) {
        parent::load($id, $field);
        if ($this->getStoreId()) {
            $this->getMultiStoreValue();
        }
//        Zend_debug::dump($this->getData());die();
        return $this;
    }

    public function getMultiStoreValue($storeId = null) {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        if (!$storeId) {
            return $this;
        }
        $storeValues = Mage::getModel('storelocator/storevalue')->getCollection()
                ->addFieldToFilter('storelocator_id', $this->getId())
                ->addFieldToFilter('store_id', $storeId);
        foreach ($storeValues as $value) {
            $this->setData($value->getAttributeCode() . '_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }
        return $this;
    }

    public function save() {
        $addressFull = $this->getFullAddress();
        if ($addressFull) {
            $address['street'] = $this->getAddress();
            $address['city'] = $this->getCity();
            $address['region'] = $this->getRegion();
            $address['zipcode'] = $this->getZipcode();
            $address['country'] = $this->getCountry();
            $currentAddress = $address['street'] . $address['city'] . $address['region'] . $address['zipcode'] . $this->getCountry();
            $currentAddress = str_replace(" ", "", $currentAddress);
            if (strcasecmp($addressFull, $currentAddress)) {
                $coordinates = Mage::getModel('storelocator/gmap')
                        ->getCoordinates($address);
                if ($coordinates) {
                    $this->setLatitude($coordinates['lat']);
                    $this->setLongtitude($coordinates['lng']);
                } else {
                    $this->setLatitude('0.000');
                    $this->setLongtitude('0.000');
                }
                return parent::save();
            }
        }
        if (!$this->getLatitude() || !$this->getLongtitude()) {
            $address['street'] = $this->getAddress();
            $address['city'] = $this->getCity();
            $address['region'] = $this->getState();
            $address['zipcode'] = $this->getZipcode();
            $address['country'] = $this->getCountry();
            $coordinates = Mage::getModel('storelocator/gmap')
                    ->getCoordinates($address);
            if ($coordinates) {
                $this->setLatitude($coordinates['lat']);
                $this->setLongtitude($coordinates['lng']);
            } else {
                $this->setLatitude('0.000');
                $this->setLongtitude('0.000');
            }
        } else {
            if ($this->getStoreLatitudeValue() || $this->getStoreLongtitudeValue()) {
                $lat = floatval($this->getStoreLatitudeValue());
                $lng = floatval($this->getStoreLongtitudeValue());
            } else {
                $lat = floatval($this->getLatitude());
                $lng = floatval($this->getLongtitude());
            }
            $this->setLatitude($lat);
            $this->setLongtitude($lng);
        }
        return parent::save();
    }

    public function getCountryName() {
        if ($this->getCountry())
            if (!$this->hasData('country_name')) {
                $country = Mage::getModel('directory/country')
                        ->loadByCode($this->getCountry());
                $this->setData('country_name', $country->getName());
            }

        return $this->getData('country_name');
    }

    public function getRegion() {
        if (!$this->getData('region')) {
            $this->setData('region', $this->getState());
        }

        return $this->getData('region');
    }

    public function getCity() {
        if (!$this->getData('city')) {
            $this->setData('city', $this->getCity());
        }

        return $this->getData('city');
    }

    public function import($option) {

        $data = $this->getData();

        //prepare status
        $data['status'] = 1;
        //check exited store
        if ($option == 1) {
            $collection = $this->getCollection()
                    ->addFieldToFilter('name', $data['name'])
                    ->addFieldToFilter('address', $data['address'])
                    ->addFieldToFilter('city', $data['city'])
                    ->addFieldToFilter('country', $data['country']);
            if (count($collection)) {
                foreach ($collection as $item) {
                    $this->setData($data);
                    $this->setId($item->getData('storelocator_id'));
                    $this->save();
                }
            }else{ 
                $this->setData($data);
                $this->save();
            }
        } else {

            $this->setData($data);
            $this->save();
        }
        return $this->getId();
    }

}

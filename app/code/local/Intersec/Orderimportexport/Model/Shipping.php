<?php

class Intersec_Orderimportexport_Model_Shipping extends Mage_Shipping_Model_Shipping
{
    public function getCarrierByCode($carrierCode, $storeId = null)
    {
        if ($carrierCode == 'imported') {
            $className = Mage::getStoreConfig('carriers/'.$carrierCode.'/model', $storeId);
            if (!$className) {
                return false;
            }
            $obj = Mage::getModel($className);
            if ($storeId) {
                $obj->setStore($storeId);
            }
            return $obj;
        } else {
            return parent::getCarrierByCode($carrierCode, $storeId);
        }
    }
}
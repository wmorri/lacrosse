<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ rswahYjrqrmpMweC('00e2096a0c1b902d00c3027ed8a139ae');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Model_Mysql4_Quotestat_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/quotestat');
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        foreach ($this->_items as $item) {
            $item->setStatus('Just abandoned');
            if($item->getAlertNumber())
            {
                $item->setStatus('Reminded '.$item->getAlertNumber().' time(s)');
            }
            if($item->getRecoveryDate())
            {
                $item->setStatus('Recovered');
            }
            if($item->getOrderDate())
            {
                $item->setStatus('Ordered');
            }
            $item->setCurrency($currency);
        }
        return $this;
    }    
    
} } 
<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ MsqPpYrWIWeijqaT('8c786adb2e8f8f91b3f6e0785fdeea80');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getGroupArray()
    {
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $db->select()->from(Mage::getSingleton('core/resource')->getTableName('customer/customer_group'), array('customer_group_id', 'customer_group_code'));
        $groupIds = array();
        foreach($db->fetchAll($select) as $group)
        {
            $groupIds[$group['customer_group_id']] = $group['customer_group_code'];
        }
        return $groupIds;
    }
} } 
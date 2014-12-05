<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ ysokiYZECEghBoDI('7def7ad41e4320829dfe5b57d6858104');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Model_Mysql4_Stoplist extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('adjcartalert/stoplist', 'id');
    }
    
    public function contains($groupId, $email)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('s' => $this->getMainTable()),'id')
            ->where('s.store_id = ?',  $groupId)
            ->where('s.customer_email = ?',  $email)
            ->limit(1);
        return $this->_getReadAdapter()->fetchOne($select);
    }     
} } 
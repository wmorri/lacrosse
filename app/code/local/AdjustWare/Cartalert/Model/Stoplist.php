<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ BsCOhYyVTVDpZCkQ('7128734d154ea934fd29b22d344f45cc');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Model_Stoplist extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/stoplist');
    }
    
    /**
     * Checks if email exists in stoplist
     *
     * @param int $groupId 
     * @param string $email eamil to check
     * @return bool
     */
    public function contains($groupId, $email)
    {
        return $this->_getResource()->contains($groupId, $email);
    }      
} } 
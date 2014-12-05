<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ MsqPpYrWIWeijqaT('b0cba63898548ce8f77c9340235f3683');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Block_Adminhtml_Dailystat_Cronmanage extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('adjcartalert/cronmanage.phtml'); 
    }
    
    public function getIsBusy()
    {   
        return Mage::getModel('adjcartalert/cronstat')->isBusy();
    }
   
} } 
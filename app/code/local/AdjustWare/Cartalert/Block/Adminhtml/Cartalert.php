<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ ashZqSeapayomhBh('ed5eee2863854c68634195bcad2a5e46');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Block_Adminhtml_Cartalert extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
      
    $this->_addButton('generate', array(
        'label'     => Mage::helper('adjcartalert')->__('Update Queue Now'),
        'onclick'   => "location.href='".$this->getUrl('*/*/generate')."';return false;",
        'class'     => '',
    ));       
      
    $this->_controller = 'adminhtml_cartalert';
    $this->_blockGroup = 'adjcartalert';
    $this->_headerText = Mage::helper('adjcartalert')->__('Alerts Queue');
    $this->_addButtonLabel = Mage::helper('adjcartalert')->__('Add Alert');
    parent::__construct();
  }
} } 
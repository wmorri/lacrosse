<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ gshWqSkOUOjoDhrR('bea5204d031ae14f48759a8077ba837f');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    parent::__construct();
    $this->_controller = 'adminhtml_history';
    $this->_blockGroup = 'adjcartalert';
    $this->_headerText = Mage::helper('adjcartalert')->__('Sent Alerts');
    $this->_removeButton('add'); 
    //d($this);

  }
} } 
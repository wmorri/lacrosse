<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ ysokiYZECEghBoDI('aabf7f69db2a99eea2e22a442b52e893');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @author Adjustware
 */ 
class AdjustWare_Cartalert_Block_Adminhtml_Stoplist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('stoplistGrid');
      $this->setDefaultSort('id');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('adjcartalert/stoplist')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $hlp =  Mage::helper('adjcartalert'); 
    $this->addColumn('id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'id',
    ));

    $this->addColumn('store_id', array(
        'header'    => $hlp->__('Specified Store Name'),
        'index'     => 'store_id',
        'type'      => 'options',
        'options'   => Mage::getSingleton('adjcartalert/source_group')->getOptionArray(),
    ));
    
    $this->addColumn('customer_email', array(
        'header'    => $hlp->__('Customer E-mail'),
        'index'     => 'customer_email',
    ));

    $this->addColumn('date', array(
        'header'    => $hlp->__('Unsibscribed On'),
        'index'     => 'date',
        'type'      => 'datetime', 
        'width'     => '150px',
        'gmtoffset' => true,
        'default'    => ' ---- ',
    ));

    return parent::_prepareColumns();
  }

  /*public function getRowUrl($row)
  {
      return $this->getUrl('* /* /edit', array('id' => $row->getId()));
  } */
  
  protected function _prepareMassaction(){
    $this->setMassactionIdField('id');
    $this->getMassactionBlock()->setFormFieldName('cartalert');
    
    $this->getMassactionBlock()->addItem('delete', array(
         'label'    => Mage::helper('adjcartalert')->__('Delete'),
         'url'      => $this->getUrl('*/*/massDelete'),
         'confirm'  => Mage::helper('adjcartalert')->__('Are you sure?')
    ));
    
    return $this; 
  }

} } 
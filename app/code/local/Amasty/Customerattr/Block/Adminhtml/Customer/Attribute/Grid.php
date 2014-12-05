<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('attributeGrid');
        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $collection->getSelect()
            ->where('main_table.is_user_defined = ?', 1)
            ->where('main_table.attribute_code != ?', 'customer_activated');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('attribute_code', array(
            'header'=>Mage::helper('catalog')->__('Code'),
            'sortable'=>true,
            'index'=>'attribute_code'
        ));

        $this->addColumn('frontend_label', array(
            'header'=>Mage::helper('catalog')->__('Label'),
            'sortable'=>true,
            'index'=>'frontend_label'
        ));

        $this->addColumn('is_visible', array(
            'header'=>Mage::helper('catalog')->__('Visible'),
            'sortable'=>true,
            'index'=>'is_visible_on_front',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));
        
        $this->addColumn('on_registration', array(
            'header'=>Mage::helper('amcustomerattr')->__('Show on Registration'),
            'sortable'=>true,
            'index'=>'on_registration',
            'type' => 'options',
            'width' => '90px',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));

        $this->addColumn('is_required', array(
            'header'=>Mage::helper('catalog')->__('Required'),
            'sortable'=>true,
            'index'=>'is_required',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));
        
        $this->addColumn('sorting_order', array(
            'header'=>Mage::helper('amcustomerattr')->__('Sorting Order'),
            'sortable'=>true,
            'index'=>'sorting_order',
            'width' => '90px',
        ));
        
        $this->addColumn('is_filterable_in_search', array(
            'header'=>Mage::helper('amcustomerattr')->__('Show on Manage Customers Grid'),
            'sortable'=>true,
            'index'=>'is_filterable_in_search',
            'type' => 'options',
            'width' => '90px',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));
        
        $this->addColumn('used_in_order_grid', array(
            'header'=>Mage::helper('amcustomerattr')->__('Show on Orders Grid'),
            'sortable'=>true,
            'index'=>'used_in_order_grid',
            'type' => 'options',
            'width' => '50px',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));
        
        $this->addColumn('on_order_view', array(
            'header'=>Mage::helper('amcustomerattr')->__('Show on Order View'),
            'sortable'=>true,
            'index'=>'on_order_view',
            'type' => 'options',
            'width' => '90px',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));
        
        $this->addColumn('used_in_product_listing', array(
            'header'=>Mage::helper('amcustomerattr')->__('Show on Billing During Checkout'),
            'sortable'=>true,
            'index'=>'used_in_product_listing',
            'type' => 'options',
            'width' => '90px',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('attribute_id' => $row->getAttributeId()));
    }

}
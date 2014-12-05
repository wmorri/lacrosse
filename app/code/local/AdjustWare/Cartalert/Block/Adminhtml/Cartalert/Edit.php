<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ escVwSmdRdZqacyN('013670464b93953794539a7b013158e6');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Block_Adminhtml_Cartalert_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; // ?
        $this->_blockGroup = 'adjcartalert';
        $this->_controller = 'adminhtml_cartalert';

        $this->_removeButton('reset');
		
        $this->_addButton('send', array(
            'label'     => Mage::helper('adjcartalert')->__('Save and Send Out'),
            'onclick'   => 'sendAndDelete()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function sendAndDelete(){
                $('edit_form').action += 'send/edit';
                editForm.submit();
            }
        ";
    }

    public function getHeaderText()
    {
            return Mage::helper('adjcartalert')->__('Abandoned Cart Alert');
    }
} } 
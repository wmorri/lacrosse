<?php

class MST_Menupro_Block_Adminhtml_Groupmenu_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('groupmenu_form', array('legend' => Mage::helper('menupro')->__('Group Information')));
        $groupMenuTypes = Mage::getSingleton('menupro/groupmenu')->getMenuTypes();
        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('menupro')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
        ));
        $fieldset->addField('menu_type', 'select', array(
        		'label' => Mage::helper('menupro')->__('Menu Type'),
        		'name' => 'menu_type',
        		'class' => 'required-entry',
        		'required' => true,
        		'values' => $groupMenuTypes
        ));
        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('menupro')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('menupro')->__('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('menupro')->__('Disabled'),
                ),
            ),
        ));
	    $fieldset->addField('description', 'textarea', array(
	        'name'      => 'description',
			'readonly'	=> true,
			'after_element_html'	=> '<small class="help-install" style="color: red; font-size: 20px;"><div class="config-heading">Click "Save And Continue Edit" button to get embed script!</div></small>'.'<small class="help-note" style="display:none;"><div class="config-heading">Copy above script and paste to where you want to show this group menu!</div></small>',
	        'label'     => Mage::helper('menupro')->__('How to embed?'),
	        'title'     => Mage::helper('menupro')->__('How to embed?'),
	        'style'     => 'width:730px; height:150px;',
	        'wysiwyg'   => false,
	        'required'  => false,
	    ));
		/* $fieldset->addField('note1', 'note', array(
          	'text'     	=> Mage::helper('menupro')->__('<b>To embed Menu Group in CMS/Static Block (Ex: Dropdown menu): <br></b><div class="config-heading" style="width:641px;">{{block type="menupro/dropdown" name="menupro" group_id="<b>group_id</b>" template="menupro/dropdown.phtml" }}</div>'),
		));
		$fieldset->addField('note2', 'note', array(
          	'text'     => Mage::helper('menupro')->__('<b>To reference in custom xml: <br></b><div class="config-heading">&lt;block type="menupro/dropdown" name="menupro" ifconfig="menupro/setting/enable"   template="menupro/dropdown.phtml" &gt;<br>&nbsp;&nbsp;&nbsp;&nbsp;  &lt;action method="setData"&gt;&lt;name&gt;group_id&lt;/name&gt;&lt;value&gt;<b>group_id</b>&lt;/value&gt;&lt;/action&gt;<br>&lt;/block&gt;</div>'),
		  
        )); */
/* 		$fieldset->addField('note3', 'note', array(
          'text'     => Mage::helper('menupro')->__('<b>Change the default template to custom layout or style: <br></b><em>template="menupro/block-title.phtml" </em>'),
		  
        ));
		$fieldset->addField('note4', 'note', array(
          'text'     => Mage::helper('menupro')->__('<b>Change Block title: <br></b><em>&lt;action method="setData"&gt;&lt;name&gt;block_title&lt;/name&gt;&lt;value&gt;Left Menu Title&lt;/value&gt;&lt;/action&gt;</em>'),
		  
        )); */
        if (Mage::getSingleton('adminhtml/session')->getMenuData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getMenuData());
            Mage::getSingleton('adminhtml/session')->setMenuData(null);
        } elseif (Mage::registry('groupmenu_data')) {
            $form->setValues(Mage::registry('groupmenu_data')->getData());
        }
        return parent::_prepareForm();
    }
}
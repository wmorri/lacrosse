<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Attribute_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('entity_attribute');

        if (!Mage::app()->isSingleStoreMode()) {
            $model->setData('stores', explode(',', $model->getData('store_ids')));
        }

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $disableAttributeFields = array(
        );

        $rewriteAttributeValue = array(
            'status'    => array(
                'is_configurable' => 0
            )
        );

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('catalog')->__('Attribute Properties'))
        );
        if ($model->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', array(
                'name' => 'attribute_id',
            ));
        }

        $this->_addElementTypes($fieldset);

        $yesno = array(
            
            array(
                'value' => 1,
                'label' => Mage::helper('catalog')->__('Yes')
            ),
            
            array(
                'value' => 0,
                'label' => Mage::helper('catalog')->__('No')
            ),
            
        );

        $fieldset->addField('attribute_code', 'text', array(
            'name'  => 'attribute_code',
            'label' => Mage::helper('catalog')->__('Attribute Code'),
            'title' => Mage::helper('catalog')->__('Attribute Code'),
            'note'  => Mage::helper('catalog')->__('For internal use. Must be unique with no spaces'),
            'class' => 'validate-code',
            'required' => true,
        ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('stores', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('cms')->__('Store View'),
                'title'     => Mage::helper('cms')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }
        else {
            $fieldset->addField('stores', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $scopes = array(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE =>Mage::helper('catalog')->__('Store View'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE =>Mage::helper('catalog')->__('Website'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL =>Mage::helper('catalog')->__('Global'),
        );

        if ($model->getAttributeCode() == 'status' || $model->getAttributeCode() == 'tax_class_id') {
            unset($scopes[Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE]);
        }

        $inputTypes = array(
            array(
                'value' => 'text',
                'label' => Mage::helper('catalog')->__('Text Field')
            ),
            array(
                'value' => 'textarea',
                'label' => Mage::helper('catalog')->__('Text Area')
            ),
            array(
                'value' => 'date',
                'label' => Mage::helper('catalog')->__('Date')
            ),
            array(
                'value' => 'multiselect',
                'label' => Mage::helper('catalog')->__('Multiple Select')
            ),
            array(
                'value' => 'multiselectimg',
                'label' => Mage::helper('catalog')->__('Multiple Checkbox Select with Images')
            ),
            array(
                'value' => 'select',
                'label' => Mage::helper('catalog')->__('Dropdown')
            ),
            array(
                'value' => 'selectimg',
                'label' => Mage::helper('catalog')->__('Single Radio Select with Images')
            ),
            array(
                'value' => 'selectgroup',
                'label' => Mage::helper('catalog')->__('Customer Group Selector')
            ),
            array(
                'value' => 'statictext',
                'label' => Mage::helper('catalog')->__('Static Text')
            ),
        );

        $response = new Varien_Object();
        $response->setTypes(array());

        $_disabledTypes = array();
        $_hiddenFields = array();
        foreach ($response->getTypes() as $type) {
            $inputTypes[] = $type;
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
            if (isset($type['disabled_types'])) {
                $_disabledTypes[$type['value']] = $type['disabled_types'];
            }
        }
        
        $ordinaryValidationRules = array(
            array(
                'value' => '',
                'label' => Mage::helper('catalog')->__('None')
            ),
            array(
                'value' => 'validate-number',
                'label' => Mage::helper('catalog')->__('Decimal Number')
            ),
            array(
                'value' => 'validate-digits',
                'label' => Mage::helper('catalog')->__('Integer Number')
            ),
            array(
                'value' => 'validate-tendigits',
                'label' => Mage::helper('catalog')->__('10 Digits Integer Number')
            ),
            array(
                'value' => 'validate-aaa-0000',
                'label' => Mage::helper('catalog')->__('AAA-0000')
            ),
            array(
                'value' => 'validate-email',
                'label' => Mage::helper('catalog')->__('Email')
            ),
            array(
                'value' => 'validate-url',
                'label' => Mage::helper('catalog')->__('Url')
            ),
            array(
                'value' => 'validate-alpha',
                'label' => Mage::helper('catalog')->__('Letters')
            ),
            array(
                'value' => 'validate-alphanum',
                'label' => Mage::helper('catalog')->__('Letters(a-zA-Z) or Numbers(0-9)')
            ),
        );
        $additionalValidationRules = array();
        $additionalValidationRules = Mage::getModel('amcustomerattr/validation')->getAdditionalValidation();
        $validationRules = array_merge($ordinaryValidationRules, $additionalValidationRules);

        $fieldset->addField('frontend_input', 'select', array(
            'name' => 'frontend_input',
            'label' => Mage::helper('catalog')->__('Catalog Input Type for Store Owner'),
            'title' => Mage::helper('catalog')->__('Catalog Input Type for Store Owner'),
            'value' => 'text',
            'values'=> $inputTypes
        ));

        $fieldset->addField('default_value_text', 'text', array(
            'name' => 'default_value_text',
            'label' => Mage::helper('catalog')->__('Default value'),
            'title' => Mage::helper('catalog')->__('Default value'),
            'value' => $model->getDefaultValue(),
        ));

        $fieldset->addField('default_value_yesno', 'select', array(
            'name' => 'default_value_yesno',
            'label' => Mage::helper('catalog')->__('Default value'),
            'title' => Mage::helper('catalog')->__('Default value'),
            'values' => $yesno,
            'value' => $model->getDefaultValue(),
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('default_value_date', 'date', array(
            'name'   => 'default_value_date',
            'label'  => Mage::helper('catalog')->__('Default value'),
            'title'  => Mage::helper('catalog')->__('Default value'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'value'  => $model->getDefaultValue(),
            'format'       => $dateFormatIso
        ));

        $fieldset->addField('default_value_textarea', 'textarea', array(
            'name' => 'default_value_textarea',
            'label' => Mage::helper('catalog')->__('Default value'),
            'title' => Mage::helper('catalog')->__('Default value'),
            'value' => $model->getDefaultValue(),
        ));
        
        $fieldset->addField('is_visible_on_front', 'select', array(
            'name'      => 'is_visible_on_front',
            'label'     => Mage::helper('catalog')->__('Visible on Front-end'),
            'title'     => Mage::helper('catalog')->__('Visible on Front-end'),
            'values'    => $yesno,
        ));

        $fieldset->addField('is_unique', 'select', array(
            'name' => 'is_unique',
            'label' => Mage::helper('catalog')->__('Unique Value'),
            'title' => Mage::helper('catalog')->__('Unique Value (not shared with other customers)'),
            'note'  => Mage::helper('catalog')->__('Not shared with other customers'),
            'values' => $yesno,
        ));

        $fieldset->addField('is_required', 'select', array(
            'name' => 'is_required',
            'label' => Mage::helper('catalog')->__('Values Required'),
            'title' => Mage::helper('catalog')->__('Values Required'),
            'values' => $yesno,
        ));

        $fieldset->addField('frontend_class', 'select', array(
            'name'  => 'frontend_class',
            'label' => Mage::helper('catalog')->__('Input Validation'),
            'title' => Mage::helper('catalog')->__('Input Validation'),
            'values'=> $validationRules 
        ));
        
        // frontend properties fieldset
        $fieldset = $form->addFieldset('front_fieldset', array('legend'=>Mage::helper('amcustomerattr')->__('Attribute Configuration')));

        $fieldset->addField('is_filterable_in_search', 'select', array(
            'name' => 'is_filterable_in_search',
            'label' => Mage::helper('amcustomerattr')->__('Show on Manage Customers Grid'),
            'title' => Mage::helper('amcustomerattr')->__('Show on Manage Customers Grid'),
            'values' => $yesno,
        ));
        
        $fieldset->addField('used_in_order_grid', 'select', array(
            'name' => 'used_in_order_grid',
            'label' => Mage::helper('amcustomerattr')->__('Show on Orders Grid'),
            'title' => Mage::helper('amcustomerattr')->__('Show on Orders Grid'),
            'values' => $yesno,
        ));
        
        $fieldset->addField('is_read_only', 'select', array(
            'name' => 'is_read_only',
            'label' => Mage::helper('amcustomerattr')->__('Is Read Only'),
            'title' => Mage::helper('amcustomerattr')->__('Is Read Only'),
            'values' => $yesno,
        ));
        
        $fieldset->addField('on_order_view', 'select', array(
            'name'  => 'on_order_view',
            'label' => Mage::helper('amcustomerattr')->__('Show on the Order View Page'),
            'title' => Mage::helper('amcustomerattr')->__('Show on the Order View Page'),
            'note'  => Mage::helper('amcustomerattr')->__('In the Account Information block at the backend'),
            'values' => $yesno,
        ));
        
        $fieldset->addField('used_in_product_listing', 'select', array(
            'name' => 'used_in_product_listing',
            'label' => Mage::helper('amcustomerattr')->__('Show on Billing During Checkout'),
            'title' => Mage::helper('amcustomerattr')->__('Show on Billing During Checkout'),
            'values' => $yesno,
        ));
        
        $fieldset->addField('on_registration', 'select', array(
            'name'  => 'on_registration',
            'label' => Mage::helper('amcustomerattr')->__('Show on Registration'),
            'title' => Mage::helper('amcustomerattr')->__('Show on Registration'),
            'values' => $yesno,
        ));
        
        $fieldset->addField('sorting_order', 'text', array(
            'name'  => 'sorting_order',
            'label' => Mage::helper('amcustomerattr')->__('Sorting Order'),
            'title' => Mage::helper('amcustomerattr')->__('Sorting Order'),
            'note'  => Mage::helper('catalog')->__('The order to display field on frontend'),
        ));
        
        if ($model->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
            $form->getElement('frontend_input')->setDisabled(1);
        }

        $form->addValues($model->getData());

        if ($model->getId() && isset($rewriteAttributeValue[$model->getAttributeCode()])) {
            foreach ($rewriteAttributeValue[$model->getAttributeCode()] as $field => $value) {
                $form->getElement($field)->setValue($value);
            }
        }

        if ($applyTo = $model->getApplyTo()) {
            $applyTo = is_array($applyTo) ? $applyTo : explode(',', $applyTo);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'apply' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_apply')
        );
    }

}
<?php
    /**
    * EcommerceTeam.com
    *
    * Seo Layered Navigation
    *
    * @category     Magento Extension
    * @copyright    Copyright (c) 2011 Ecommerce Team (http://www.ecommerce-team.com)
    * @author       Ecommerce Team
    * @version      3.0
    */


class EcommerceTeam_Sln_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Main extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main
{
    /**
     * Adding product form elements for editing attribute
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $helper   = Mage::helper('ecommerceteam_sln');
        $form     = $this->getForm();
        $fieldset = $form->addFieldset('sln_fieldset', array('legend'=>Mage::helper('catalog')->__('SEO Navigation Properties')));

        $fieldset->addField('group_id', 'select', array(
            'name'  => 'group_id',
            'label' => Mage::helper('catalog')->__('Filter Group'),
            'title' => Mage::helper('catalog')->__('Filter Group'),
            'values'=> array(
                EcommerceTeam_Sln_Block_Layer_View::NAVIGATION_GROUP_DEFAULT    => $this->__('Left'),
                EcommerceTeam_Sln_Block_Layer_View::NAVIGATION_GROUP_TOP        => $this->__('Top'),
                EcommerceTeam_Sln_Block_Layer_View::NAVIGATION_GROUP_RIGHT      => $this->__('Right'),
            ),
        ));

        $typeField = $fieldset->addField('frontend_type', 'select', array(
            'name'  => 'frontend_type',
            'label' => Mage::helper('catalog')->__('Frontend Type'),
            'title' => Mage::helper('catalog')->__('Frontend Type'),
            'values'=> array(
                EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_DEFAULT    => $this->__('Default'),
                EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_DROPDOWN   => $this->__('Dropdown'),
                EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_CHECKBOX   => $this->__('Checkbox'),
                EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_IMAGE      => $this->__('Image'),
                EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_INPUT      => $this->__('Input'),
                EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_SLIDER     => $this->__('Slider'),
            ),
        ));

        $typeField->setData('after_element_html', '<script type="text/javascript">
            isDecimal = false;
            if ($("frontend_input").value != "price") {
                var options = $("frontend_type").select("option");
                for (var i = 0; i < options.length; i++) {
                    e = options[i];
                    if (e.value == "' . EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_INPUT . '" || e.value == "' . EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_SLIDER . '") {
                        e.parentNode.removeChild(e);
                    }
                };
            } else {
                isDecimal = true;
            }

            Event.observe($("frontend_input"), "change", function(e){
                if (this.value == "price") {
                    if (!isDecimal) {
                        var option = document.createElement("option");
                        option.value = "'.EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_INPUT.'";
                        option.innerHTML = "'.$helper->__('Input').'";
                        $("frontend_type").appendChild(option);

                        var option = document.createElement("option");
                        option.value = "'.EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_SLIDER.'";
                        option.innerHTML = "'.$helper->__('Slider').'";
                        $("frontend_type").appendChild(option);

                        isDecimal = true;
                    }
                } else {
                    isDecimal = false;
                    var options = $("frontend_type").select("option");
                    for(var i = 0; i < options.length; i++){
                        e = options[i];
                        if (e.value == "'.EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_INPUT.'" || e.value == "'.EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_SLIDER.'") {
                            e.parentNode.removeChild(e);
                        }
                    };
                }
            });
        </script>');

        $fieldset->addField('comment', 'textarea', array(
            'name'  => 'comment',
            'label' => Mage::helper('catalog')->__('Info Text'),
            'title' => Mage::helper('catalog')->__('Info Text'),
        ));

        return $this;
    }
}

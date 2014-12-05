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

class EcommerceTeam_Sln_Model_System_Config_Source_Category_Type
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $helper = Mage::helper('ecommerceteam_sln');

        return array(
            array(
                'value' => EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_DEFAULT,
                'label' => $helper->__('Default')),
            array(
                'value' => EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_CHECKBOX,
                'label' => $helper->__('Checkbox')),
            array(
                'value' => EcommerceTeam_Sln_Model_Attribute::FRONTEND_TYPE_DROPDOWN,
                'label' => $helper->__('Dropdown')),
        );
    }

}

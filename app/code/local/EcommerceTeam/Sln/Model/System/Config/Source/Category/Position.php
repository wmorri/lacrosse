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

class EcommerceTeam_Sln_Model_System_Config_Source_Category_Position
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'default', 'label'=>Mage::helper('adminhtml')->__('Left')),
            array('value' => 'top', 'label'=>Mage::helper('adminhtml')->__('Top')),
            array('value' => 'right', 'label'=>Mage::helper('adminhtml')->__('Right')),
        );
    }

}

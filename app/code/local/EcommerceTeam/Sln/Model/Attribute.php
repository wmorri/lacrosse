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

class EcommerceTeam_Sln_Model_Attribute extends Mage_Core_Model_Abstract
{
    const FRONTEND_TYPE_DEFAULT     = 'default';
    const FRONTEND_TYPE_CHECKBOX    = 'checkbox';
    const FRONTEND_TYPE_DROPDOWN    = 'dropdown';
    const FRONTEND_TYPE_IMAGE       = 'image';
    const FRONTEND_TYPE_INPUT       = 'input';
    const FRONTEND_TYPE_SLIDER      = 'slider';

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecommerceteam_sln/attribute');
    }

}

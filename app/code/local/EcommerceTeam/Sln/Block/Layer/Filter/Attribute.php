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

class EcommerceTeam_Sln_Block_Layer_Filter_Attribute extends EcommerceTeam_Sln_Block_Layer_Filter_Abstract
{
    protected $_optionCollection;

    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'ecommerceteam_sln/layer_filter_attribute';
    }

    protected function _prepareFilter()
    {
        $this->_filter->setAttributeModel($this->getAttributeModel());
        parent::_prepareFilter();
        return $this;
    }
    public function getAdvancedOptionCollection()
    {
        if (is_null($this->_optionCollection)) {
            $this->_optionCollection = Mage::getResourceModel('ecommerceteam_sln/attribute_collection');
            $this->_optionCollection->addFieldToFilter('attribute_id', $this->getAttributeModel()->getAttributeId());
        }
        return $this->_optionCollection;
    }
}

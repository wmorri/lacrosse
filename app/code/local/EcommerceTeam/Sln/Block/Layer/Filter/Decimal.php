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

class EcommerceTeam_Sln_Block_Layer_Filter_Decimal extends EcommerceTeam_Sln_Block_Layer_Filter_Abstract
{

    protected $_minMaxValue = array();

    /**
     * Initialize Decimal Filter Model
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'catalog/layer_filter_decimal';
    }

    /**
     * Prepare filter process
     *
     * @return EcommerceTeam_Sln_Block_Layer_Filter_Decimal
     */
    protected function _prepareFilter()
    {
        $this->_filter->setAttributeModel($this->getAttributeModel());
        parent::_prepareFilter();
        return $this;
    }

    /**
     * Get current minimal price
     *
     * @return float
     */
    public function getMinPriceInt()
    {
        return floatval($this->_filter->getMinValue());
    }

    /**
     * Get current maximal value
     *
     * @return float
     */
    public function getMaxPriceInt()
    {
        return floatval($this->_filter->getMaxValue());
    }
}

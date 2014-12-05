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

class EcommerceTeam_Sln_Block_Layer_Filter_Price extends EcommerceTeam_Sln_Block_Layer_Filter_Abstract
{
    /**
     * Initialize Price filter module
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->_filterModelName = 'ecommerceteam_sln/layer_filter_price';
    }

    /**
     * Prepare filter process
     *
     * @return EcommerceTeam_Sln_Block_Layer_Filter_Price
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
        return floatval($this->_filter->getMinPriceInt());
    }

    /**
     * Get current maximal value
     *
     * @return float
     */
    public function getMaxPriceInt()
    {
        return floatval($this->_filter->getMaxPriceInt());
    }
}

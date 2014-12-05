<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_Quickviewpro
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */


class Belvg_Quickviewpro_Block_Quickview extends Mage_Catalog_Block_Product_View
{
    /**
     * The main extension settings
     *
     * @var array
     */
    protected $_settings = array();

    /**
     * Set main quickview settings
     * Set quickview navigation options
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $this->_settings = $this->helper('quickviewpro')->getSettings();
       // $this->searchNextPrevious();
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }
}
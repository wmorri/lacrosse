<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml newsletter subscribers grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magebuzz_Subscribepopup_Adminhtml_Block_Subscriber_Grid extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
	protected function _prepareColumns() // tried public too
	{

		parent::_prepareColumns();



		$this->addColumn('firstname', array(
            'header'    => Mage::helper('newsletter')->__('Firstname'),
            'index'     => 'firstname'
        ));
		$this->addColumn('lastname', array(
            'header'    => Mage::helper('newsletter')->__('Lastname'),
            'index'     => 'lastname'
        ));
		$this->addColumn('zipcode', array(
            'header'    => Mage::helper('newsletter')->__('Zipcode'),
            'index'     => 'zipcode'
        ));

		return $this; #parent::_prepareColumns();
	}
}
<?php
/*
* @copyright   Copyright ( c ) 2013 www.magebuzz.com
*/

class Magebuzz_Subscribepopup_Model_Subscribepopup extends Mage_Core_Model_Abstract {
	public function _construct() {
			parent::_construct();
			$this->_init('subscribepopup/subscribepopup');
	}
	
	public function createCouponCode() {
		$usesPerCustomer = 1;
		$discountType = 2;
		$discountAmount	= Mage::helper('subscribepopup')->getDiscountAmount();
		$couponExpireIn	= Mage::helper('subscribepopup')->getCouponExpireIn();
		$usesPerCoupon	= Mage::helper('subscribepopup')->getUsesPerCoupon();
		$couponLength = Mage::helper('subscribepopup')->getLengthCouponCode();
		$simpleAction	= Mage::helper('subscribepopup')->getDiscountType();
		$fromDate = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
		$toDate = Mage::helper('subscribepopup')->getDateToRemoveCoupon($fromDate, $couponExpireIn);
		
		$customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
		$found = false;

		foreach ($customerGroups as $group) {
			if ($group['value'] == 0) {
				$found = true;
			}
		}
		if (!$found) {
			array_unshift($customerGroups, array(
				'value' => 0,
				'label' => Mage::helper('salesrule')->__('NOT LOGGED IN'))
			);
		}
		
		$customerGroupIds = Mage::getResourceModel('customer/group_collection')->toOptionArray();
		$group = array();
		
		foreach($customerGroupIds as $cusGroup) {
			$group[] = $cusGroup['value'];
		}
		$model = Mage::getModel('salesrule/rule');
		$couponCode = $this->generateCoupon($couponLength);
		$model->setName($couponCode);
		$model->setDescription("Discount coupon for subscriber.");
		$model->setIsActive(1);
		$model->setWebsiteIds('1');
		$model->setCustomerGroupIds($group);
		$model->setCouponType(2);
		$model->setCouponCode($couponCode);
		$model->setUsesPerCoupon($usesPerCoupon);
		$model->setUsesPerCustomer($usesPerCustomer);
		$model->setFromDate($fromDate);
		$model->setToDate($toDate);
		$model->setIsRss(0);
		
		$model->setSimpleAction($simpleAction);
		$model->setDiscountAmount($discountAmount);
		$model->setDiscountQty(0);
		$model->setDiscountStep(0);
		$model->setApplyToShipping(0);
		$model->setSimpleFreeShipping(0);
		$model->setStopRulesProcessing(0);
		
		$model->setIsAdvanced(1);
		$model->setSortOrder('0');
		$model->setTimesUsed(0);        
		
		$model->save();
		return $couponCode;
	}
	
	public function generateCoupon($length = null) {
		$rndId = crypt(uniqid(rand(),1));
		$rndId = strip_tags(stripslashes($rndId));
		$rndId = str_replace(array(".", "$"),"",$rndId);
		$rndId = strrev(str_replace("/","",$rndId));
		if (!is_null($rndId)){
			return strtoupper(substr($rndId, 0, $length));
		}
		return strtoupper($rndId);
	}
}
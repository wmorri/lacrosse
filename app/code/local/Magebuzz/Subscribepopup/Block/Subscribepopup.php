<?php
/*
* @copyright   Copyright ( c ) 2013 www.magebuzz.com
*/
class Magebuzz_Subscribepopup_Block_Subscribepopup extends Mage_Newsletter_Block_Subscribe {
	public function _prepareLayout() {
		return parent::_prepareLayout();
	}
	
	public function canShowPopup() {
		if ($this->_isInHomepage()) {
			return true;
		}
		else if (Mage::helper('subscribepopup')->isShowAllPage()) {
				return true;			
		}
		return false;
	}
	
	protected function _isInHomepage() {
		$routeName = Mage::app()->getFrontController()->getRequest()->getRouteName();
		if ($routeName == 'cms' && Mage::getSingleton('cms/page')->getIdentifier() == 'home') {
			return true;
		}
		return false;
	}
	
	public function useStaticBlockContent() {
		return Mage::helper('subscribepopup')->useStaticBlock();
	}
	
	public function getStaticBlockId() {
		return Mage::helper('subscribepopup')->getPopupStaticBlock();
	}
}
<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Subscribepopup_Model_System_Config_Cmspage {
	public function toOptionArray() {
		$cms_pages = Mage::getModel('cms/block')->getCollection();
			// ->setFirstStoreFlag(true);
		$options = array();
		foreach ($cms_pages as $cms_page) {
			$options[] = array(
				'value' => $cms_page->getId(),
				'label' => $cms_page->getTitle(),
			);
		}		
		return $options;
	}
}
<?php
/*
* @copyright   Copyright ( c ) 2013 www.magebuzz.com
*/

class Magebuzz_Subscribepopup_Model_Observer {
	public function loadSubscribeForm($observer){
		if (Mage::app()->getRequest()->getModuleName() == 'subscribepopup') {
			die('subscribepopup');
		}
		else{
			die('adadsada');
		}
	}
}
<?php
/*
* @copyright   Copyright ( c ) 2013 www.magebuzz.com
*/

class Magebuzz_Subscribepopup_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction() {	
		$this->loadLayout();  
		$this->renderLayout();
	}
}
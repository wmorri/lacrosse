<?php
/*
* @copyright   Copyright ( c ) 2013 www.magebuzz.com
*/

require_once Mage::getModuleDir('controllers', 'Mage_Newsletter').DS.'SubscriberController.php';
class Magebuzz_Subscribepopup_SubscriberController extends Mage_Newsletter_SubscriberController {
	public function newAction() {
		parent::newAction();
		$timeCookiesTimeout = Mage::helper('subscribepopup')->timeCookiesTimeout();
		
		//Set or remove cookie
		if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
			$email = (string) $this->getRequest()->getPost('email');
			$period = $timeCookiesTimeout*86400;
			Mage::getModel('core/cookie')->set('email_subscribed', $email, $period);
			if ($this->getRequest()->getPost('subscriber_firstname') || $this->getRequest()->getPost('subscriber_lastname')) {
				
				$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
				$firstname = (string) $this->getRequest()->getPost('subscriber_firstname');
				$lastname = (string) $this->getRequest()->getPost('subscriber_lastname');
				$zipcode = (string) $this->getRequest()->getPost('subscriber_zipcode');
				
				$subscriber->setFirstname($firstname);
				$subscriber->setLastname($lastname);
				$subscriber->setZipcode($zipcode);
				$subscriber->save();
			}
		}
		else{
			//Remove cookie
			if (isset($_COOKIE['email_subscribed']))
				setcookie('email_subscribed',$email,time()-$timeCookiesTimeout*86400,'/');
		}
	}
}
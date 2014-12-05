<?php
/**
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_CustomerSynchronisation_Model_Observer
{
	/**
	 * Synchronise the customer trying to login
	 * If they exist in WP but not Mage, copy over to Mage
	 * If they exist in WP but not Mage, they are NOT copied to WP
	 *
	 */
	public function customerLogin(Varien_Event_Observer $observer)
	{
		if (!$this->isCustomerSynchronisationEnabled()) {
			return false;
		}

		try {
			if ($login = Mage::app()->getRequest()->getPost('login')) {
				$login = new Varien_Object($login);

				$customerId = $this->getMagentoCustomerIdByEmail($login->getUsername());

				if (is_null($customerId) || !$customerId) {
					if ($user = $this->getWordPressUserModelByEmail($login->getUsername())) {
						if ($this->isValidWordPressPassword($login->getPassword(), $user->getUserPass())) {
							$this->_copyCustomerFromWordPress($user, $login->getPassword());
						}
					}
				}
				else {
					$this->_copyCustomerToWordPress($customerId, $login);
				}
			}
		}
		catch (Exception $e) {
			Mage::helper('wordpress/log')->log($e->getMessage());
			Mage::logException($e);
		}
	}
	
	/**
	 * Initialise values needed to synchronise Magento/WordPress user records
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function initCustomerUpdate(Varien_Event_Observer $observer)
	{
		if (!$this->isCustomerSynchronisationEnabled()) {
			return false;
		}
		
		$customer = $observer->getEvent()->getCustomer();
		
		$customer->setOriginalFirstname($customer->getFirstname());
		$customer->setOriginalLastname($customer->getLastname());
		$customer->setOriginalEmail($customer->getEmail());
		$customer->setOriginalPasswordHash($customer->getPasswordHash());
		
		if ($user = $this->getWordPressUserModelByEmail($customer->getEmail())) {
			$customer->setWordpressUser($user);
		}
	}
	
	/**
	 * Synchronise customer data when updating
	 *
	 */
	public function customerUpdate(Varien_Event_Observer $observer)
	{
		if (!$this->isCustomerSynchronisationEnabled()) {
			return false;
		}

		$session = Mage::getSingleton('customer/session');
		
		if ($session->isLoggedIn()) {
			$customer = $session->getCustomer();
			$user = Mage::getModel('wordpress/user');
			$user->load($customer->getOriginalEmail(), 'user_email');

			if (!$user->getId()) {
				$this->_copyCustomerToWordPress($customer, new Varien_Object(array('password' => $customer->getPassword())));
			}
			else {
				$user->setUserEmail($customer->getEmail());
				$user->setFirstName($customer->getFirstname());
				$user->setLastName($customer->getLastname());
				
				if ($customer->getOriginalEmail() == $user->getUserLogin()) {
					$user->setUserLogin($customer->getEmail());
				}
				
				$user->getMeta('nickname');
				
				if (!$user->getNickname() || $customer->getOriginalFirstname() == $user->getNickname()) {
					$user->setNickname($customer->getFirstname());
				}
				
				try {
					if ($customer->hasPassword()) {
						if ($customer->validatePassword($customer->getPassword())) {
							$wpHash = $this->hashPasswordForWordPress($customer->getPassword());
							
							if ($this->isValidWordPressPassword($customer->getPassword(), $wpHash)) {
								$user->setUserPass($wpHash);
							}
						}
					}
				}
				catch (Exception $e) {
					Mage::helper('wordpress')->log($e->getMessage());
					Mage::logException($e);
				}
				
				try {
					$user->save();
				}
				catch (Exception $e) {
					Mage::helper('wordpress')->log($e->getMessage());
					Mage::logException($e);
				}
			}
		}
		else {
			if ($customer = $observer->getEvent()->getCustomer()) {
				$user = Mage::getModel('wordpress/user');
				$user->load($customer->getOriginalEmail(), 'user_email');
				
				if ($customer->hasPassword()) {
				
					try {
						if ($customer->validatePassword($customer->getPassword())) {
							$wpHash = $this->hashPasswordForWordPress($customer->getPassword());
							
							if ($this->isValidWordPressPassword($customer->getPassword(), $wpHash)) {
								$user->setUserPass($wpHash);
								
								try {
									$user->save();
								}
								catch (Exception $e) {
									Mage::helper('wordpress')->log($e->getMessage());
									Mage::logException($e);
								}
							}
						}
					}
					catch(Exception $e) {
						Mage::helper('wordpress')->log($e->getMessage());
						Mage::logException($e);
					}
				}
			}
		}
	}
	
	/**
	 * Synchronise a customer after purchasing via the checkout
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function saveCustomerFromOnepage(Varien_Event_Observer $observer)
	{
		if (!$this->isCustomerSynchronisationEnabled()) {
			return false;
		}
		
		$quote = $observer->getEvent()->getQuote();
		$customer = $quote->getCustomer();

		$login = new Varien_Object(array('username' => $customer->getEmail(), 'password' => $customer->decryptPassword($quote->getPasswordHash())));
	
		try {
			$this->_copyCustomerToWordPress($customer, $login);
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e->getMessage());
		}
	}
	
	/**
	 * Retrieve a WordPress user model based on an email address
	 *
	 * @param string $email
	 * @return Fishpig_Wordpress_Model_User
	 */
	public function getWordPressUserModelByEmail($email)
	{
		$user = Mage::getModel('wordpress/user')->load($email, 'user_email');
		
		return $user->getId() ? $user : false;
	}

	/**
	 * Retrieve a Magento customer ID by email
	 *
	 * @param string $email
	 * @return int
	 */
	public function getMagentoCustomerIdByEmail($email)
	{
		$select = $this->getMagentoReadAdapter()
			->select()
			->from($this->getMagentoTableName('customer_entity'), 'entity_id')
			->where('email=?', $email)
			->limit(1);

		return $this->getMagentoReadAdapter()->fetchOne($select);
	}
	
	/**
	 * Copies the basic information from WordPress to Magento
	 *
	 * @param Fishpig_Wordpress_Model_User $user
	 * @param string $password
	 * @return false | Mage_Customer_Model_Customer
	 */
	protected function _copyCustomerFromWordPress($user, $password)
	{
		$customer = Mage::getModel('customer/customer');
			
		$customer->setEmail($user->getUserEmail())
			->setPassword($password)
			->setFirstname($user->getMeta('first_name'))
			->setLastname($user->getMeta('last_name'))
			->setStoreId(Mage::app()->getStore()->getId());
		
		try {
			$customer->save();
			
			return $customer;
		}
		catch (Exception $e) {
			return false;
		}
	}
	
	/**
	 * Copy the customer to WordPress
	 *
	 * @param int $customerId
	 * @param Varien_Object $login
	 */
	protected function _copyCustomerToWordPress($customer, Varien_Object $login)
	{
		if (!is_object($customer)) {
			$customer = Mage::getModel('customer/customer')->load($customer);
		}
		
		if ($customer->getId()) {
			$user = Mage::getModel('wordpress/user');
			
			$user->load($customer->getEmail(), 'user_email');
			
			if (!$user->getId()) {
				$user->setUserLogin($customer->getEmail());
				$user->setUserPass($this->hashPasswordForWordPress($login->getPassword()));
				$user->setUserNicename($customer->getName());
				$user->setUserEmail($customer->getEmail());
				$user->setUserRegistered($customer->getCreatedAt());
				$user->setUserStatus(0);
				$user->setDisplayName($customer->getFirstname());
				$user->setFirstName($customer->getFirstname());
				$user->setLastName($customer->getLastname());
				$user->setRole($this->getDefaultUserRole());
				$user->setUserLevel(0);
				$user->setNickname($customer->getFirstname());

				try {
					$user->save();
				}
				catch (Exception $e) {
					Mage::helper('wordpress')->log($e->getMessage());
					Mage::logException($e);
				}
			}
		}
	}
	
	/**
	 * Retrieve the default user role from the WordPress Database
	 *
	 * @return string
	 */
	public function getDefaultUserRole()
	{
		$role = Mage::helper('wordpress')->getWpOption('default_role', 'subscriber');
		
		if (!$role) {
			return 'subscriber';
		}
		
		return $role;
	}

	/**
	 * Retrieve the read adapter for the Magento database
	 */
	public function getMagentoReadAdapter()
	{
		return Mage::getSingleton('core/resource')->getConnection('core_read');
	}

	/**
	 * Retrieve the read adapter for the WordPress database
	 */	
	public function getWordPressReadAdapter()
	{
		if (Mage::helper('wordpress')->isSameDatabase()) {
			return $this->getMagentoReadAdapter();
		}
		
		return Mage::helper('wordpress/database')->getReadAdapter();
	}
	
	/**
	 * Retrieve the write adapter for the Magento database
	 */
	public function getMagentoWriteAdapter()
	{
		return Mage::getSingleton('core/resource')->getConnection('core_write');
	}
	
	/**
	 * Retrieve Magento table name
	 *
	 * @return string
	 */
	public function getMagentoTableName($table)
	{
		return Mage::getSingleton('core/resource')->getTableName($table);
	}
	
	/**
	 * Retrieve a WordPress table name
	 *
	 * @return string
	 */
	public function getWordPressTableName($table)
	{
		return Mage::helper('wordpress/database')->getTableName($table);
	}
	
	/**
	 * Test whether can open the PhPassword file
	 *
	 * @return bool
	 */
	public function canOpenPhPasswordFile()
	{
		try {
			if ($this->_requirePhPassClass()) {
				return true;
			}
		}
		catch (Exception $e) {
			if (Mage::getDesign()->getArea() == 'adminhtml') {
				Mage::getSingleton('adminhtml/session')->addError('Customer Synch Error: ' . $e->getMessage());
			}
			
			Mage::helper('wordpress')->log("There was an error including your PhPassword file (see error in entry below)");
			Mage::helper('wordpress')->log($e->getMessage());
		}
		
		return false;
	}
	
	/**
	 * Force inclusion of WordPress Password class file
	 */
	protected function _requirePhPassClass()
	{
		if (is_null(Mage::registry('_wordpress_require_phpass'))) {
			$classFile = Mage::helper('wordpress')->getWordPressPath() . 'wp-includes/class-phpass.php';

			if (file_exists($classFile) && is_readable($classFile)) {
				require_once($classFile);
				Mage::register('_wordpress_require_phpass', true, true);
			}
			else {
				Mage::register('_wordpress_require_phpass', false, true);
				Mage::helper('wordpress')->log(Mage::helper('wordpress')->__('Error including password file (%s)', $classFile));
			}
		}
		
		return Mage::registry('_wordpress_require_phpass');
	}

	/**
	 * Returns true if the password can be hashed to equal $hash
	 *
	 * @oaram string $password
	 * @param string(hash) $hash
	 * @return bool
	 */
	public function isValidWordPressPassword($password, $hash)
	{
		$this->_requirePhPassClass();
						
		$wpHasher = new PasswordHash(8, TRUE);

		return $wpHasher->CheckPassword($password, $hash) ? true : $hash == md5($password);
	}
	
	/**
	 * Convert a string to a valid WordPress password hash
	 *
	 * @param string $password
	 * @return string
	 */
	public function hashPasswordForWordPress($password)
	{
		$this->_requirePhPassClass();
		
		if (class_exists('PasswordHash')) {
			$wpHasher = new PasswordHash(8, TRUE);
		
			return $wpHasher->HashPassword($password);
		}
		
		throw new Exception('Cannot find class PasswordHash');
	}
	
	/**
	 * Determine whether synchronisation can be ran
	 *
	 * @return bool
	 */
	public function isCustomerSynchronisationEnabled()
	{
		if (Mage::helper('wpCustomerSynch')->isEnabled()) {
			if ($this->canOpenPhPasswordFile()) {
				return true;
			}
		}

		return false;
	}
}

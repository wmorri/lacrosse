<?php


require getcwd().'/lib/openId/openid.php';
require getcwd().'/lib/Facebook/Facebook.php';
//require getcwd().'/lib/Facebook/Exception.php';
require_once(getcwd().'/lib/twitteroauth/twitteroauth.php');  
      

class Belvg_Easyauth_UserController extends Mage_Core_Controller_Front_Action
{

	private $consumer_key;
    private $consumer_secret;
    private $oauth_callback;

	public function _init(){
		$this->consumer_secret = Mage::getStoreConfig('easyauth/twitter/conssecret');
		$this->consumer_key  = Mage::getStoreConfig('easyauth/twitter/conskey');
		$this->oauth_callback = Mage::getStoreConfig('easyauth/twitter/oauthcallback');        
	}

  public function mainAction(){
      $request = $this->getRequest()->getParam('p');
      if ( $request == 'google')
              $this->_redirect('*/*/loginpro/',array('pro'=>'1'));
	  if ( $request == 'twitter')
			 $this->_redirect('*/*/twitterconnect/');		
      $this->loadLayout();
      $this->getLayout()->getBlock('root')->setTemplate('page/empty.phtml');
      $newsBlock = $this->getLayout()->createBlock('easyauth/main')
            ->setTemplate('easyauth/services/'.$request.'.phtml');
      $this->getLayout()->getBlock('content')->append($newsBlock);
      $this->renderLayout();
  }
  public function loginAction(){		
		//if ($_POST['service_url'] == 'twitter.com' or $_SESSION['status'] == 'verified') 		
        try {
            $openid = new LightOpenID;
            if(!$openid->mode) {
                if(isset($_POST['openid_identifier'])) {
					Mage::getSingleton('core/session')->setOident($_POST['openid_identifier']);
                    $service_url = $_POST['service_url'];
                    $openid->required = array('namePerson/friendly', 'contact/email');
                    $openid->optional = array('namePerson/first');                    
                    if ($service_url == 'clavid.com' || $service_url == 'wordpress.com' || $service_url == 'livejournal.com' || $service_url == 'myopenid.com')
                        $openid->identity = $_POST['openid_identifier'].'.'.$service_url;
                    else
                        $openid->identity = $service_url.$_POST['openid_identifier'];
                    $this->_redirectUrl($openid->authUrl());
                }
            } elseif($openid->mode == 'cancel') {
                echo 'User has canceled authentication!';
            } else {
                echo 'User ' . ($openid->validate() ? $openid->identity . ' has ' : 'has not ') . 'logged in.';                		
				$this->userLog($openid->getAttributes());
            }
        } catch(ErrorException $e) {
            echo $e->getMessage();
        }
  }
  public function facebookconnectAction() {		
		$me = null;
		$cookie = $this->get_facebook_cookie(Mage::getStoreConfig('easyauth/facebook/appid'), Mage::getStoreConfig('easyauth/facebook/secret'));
        $me = (array) json_decode(file_get_contents('https://graph.facebook.com/me?access_token=' . $cookie['access_token']));
		/* if (!is_null($me)) {
            $session = Mage::getSingleton('customer/session');
		} */
/* print_r($me);die(); */		
		if (is_array($me)) {
			$_email = $me['email'];
			$fname = $me['first_name'];
			$lname = $me['last_name'];
			$_customer =  Mage::getModel('easyauth/main')->checkExist($_email);
			if (!$_customer->getId())
				$this->createFUser($me);
			else
				$this->loginUser($_customer);
		}
		echo '<script>opener.parent.location.reload();window.close();</script>';
    }
	private function get_facebook_cookie($app_id, $app_secret)
    {
        if ($_COOKIE['fbsr_' . $app_id] != '') {
            return $this->get_new_facebook_cookie($app_id, $app_secret);
        } else {
            return $this->get_old_facebook_cookie($app_id, $app_secret);
        }
    }

    private function get_old_facebook_cookie($app_id, $app_secret)
    {
        $args = array();
        parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
        ksort($args);
        $payload = '';
        foreach ($args as $key => $value) {
            if ($key != 'sig') {
                $payload .= $key . '=' . $value;
            }
        }
        if (md5($payload . $app_secret) != $args['sig']) {
            return array();
        }
        return $args;
    }

    private function get_new_facebook_cookie($app_id, $app_secret)
    {
        $signed_request = $this->parse_signed_request($_COOKIE['fbsr_' . $app_id], $app_secret);
        // $signed_request should now have most of the old elements
        $signed_request[uid] = $signed_request[user_id]; // for compatibility
        if (!is_null($signed_request)) {
            // the cookie is valid/signed correctly
            // lets change "code" into an "access_token"
            $access_token_response = file_get_contents("https://graph.facebook.com/oauth/access_token?client_id=$app_id&redirect_uri=&client_secret=$app_secret&code=$signed_request[code]");
            parse_str($access_token_response);
            $signed_request[access_token] = $access_token;
            $signed_request[expires] = time() + $expires;
        }
        return $signed_request;
    } 
	
	private function parse_signed_request($signed_request, $secret)
    {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        // decode the data
        $sig = $this->base64_url_decode($encoded_sig);
        $data = json_decode($this->base64_url_decode($payload), true);

        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            error_log('Unknown algorithm. Expected HMAC-SHA256');
            return null;
        }

        // check sig
        $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
        if ($sig !== $expected_sig) {
            error_log('Bad Signed JSON signature!');
            return null;
        }

        return $data;
    }

    private function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
  
  
  
    public function twitterconnectAction(){
		$this->_init();     
        if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
            $this->_redirect('*/*/clearsession/');
        }
        /* Get user access tokens out of the session. */
        $access_token = $_SESSION['access_token'];

        /* Create a TwitterOauth object with consumer/user tokens. */
        $connection = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);

        /* If method is set change API call made. Test is called by default. */
        $content = $connection->get('account/verify_credentials');       
        if ($content->id){
            $this->tuserLog($content);
            echo '<script>opener.parent.location.reload();window.close();</script>';
        }
  }
  
   public function clearsessionAction(){
        //unset($_SESSION['access_token']);
        $this->_redirect('*/*/connect/');
    }

    public function connectAction(){
        $this->_init();
        if ($this->consumer_key === '' || $this->consumer_secret === '') {
          echo 'You need a consumer key and secret to test the sample code. Get one from <a href="https://twitter.com/apps">https://twitter.com/apps</a>';
          exit;
        }
        
        $connection = new TwitterOAuth($this->consumer_key, $this->consumer_secret);		
        /* Get temporary credentials. */
        $request_token = $connection->getRequestToken();        
        /* Save temporary credentials to session. */
        $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        /* If last connection failed don't display authorization link. */
        switch ($connection->http_code) {
          case 200:
            /* Build authorize URL and redirect user to Twitter. */
            $url = $connection->getAuthorizeURL($token);
            $this->_redirectUrl($url);
            break;
          default:
            /* Show notification if something went wrong. */
            echo 'Could not connect to Twitter. Refresh the page or try again later.';
        }
    }

    public function callbackAction(){
        $this->_init();
        /* If the oauth_token is old redirect to the connect page. */
        if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
          $_SESSION['oauth_status'] = 'oldtoken';
          $this->_redirect('*/*/clearsession/');
        }

        /* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
        $connection = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

        /* Request access tokens from twitter */
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $_SESSION['access_token'] = $access_token;

        /* Remove no longer needed request tokens */
        unset($_SESSION['oauth_token']);
        unset($_SESSION['oauth_token_secret']);

        /* If HTTP response is 200 continue otherwise send to connect page to retry */
        if (200 == $connection->http_code) {
          /* The user has been verified and the access tokens can be saved for future use */
          $_SESSION['status'] = 'verified';
          $this->_redirect('*/*/twitterconnect/');
        } else {
          /* Save HTTP status for error dialog on connnect page.*/
          $this->_redirect('*/*/clearsession/');
        }
    }
  
   public function loginproAction(){

        try {
            $openid = new LightOpenID;
            if(!$openid->mode) {
                if($this->getRequest()->getParam('pro')) {
                    $openid->required = array('namePerson/friendly', 'contact/email');
                    $openid->optional = array('namePerson/first');
                    $openid->identity = 'https://www.google.com/accounts/o8/id';                    
                    $this->_redirectUrl($openid->authUrl());
                }
            } elseif($openid->mode == 'cancel') {
                echo 'User has canceled authentication!';
            } else {
                echo 'User ' . ($openid->validate() ? $openid->identity . ' has ' : 'has not ') . 'logged in.';                
				$this->userLog($openid->getAttributes());
            }
        } catch(ErrorException $e) {
            echo $e->getMessage();
        }
  }

    private function userLog($_data){				
		$_email = ($_data['contact/email']) ? $_data['contact/email'] : '';	
		if (Mage::getSingleton('core/session')->getOident() && $_email == '')
			$_email = Mage::getSingleton('core/session')->getOident();
        $_customer =  Mage::getModel('easyauth/main')->checkExist($_email);				
        if (!$_customer->getId())
            $this->createUser($_data,$_email);
        else
            $this->loginUser($_customer);
		echo '<script>opener.parent.location.reload();window.close();</script>';
    }
	
	private function tuserLog($_data){
		$_email = $_data->screen_name;
        $_customer =  Mage::getModel('easyauth/main')->checkExist($_email);
        if (!$_customer->getId())
            $this->createTUser($_data);
        else
            $this->loginUser($_customer);       

    }
	
	
	
	private function createTUser($_data){        
        $customer = Mage::getModel('customer/customer');
        $password = '';
        $email = $_data->screen_name;

        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());        
        $customer->setEmail($email);
        $customer->setFirstname($_data->name);
        $customer->setLastname('');
        $customer->setPassword($password);
        try {
            $customer->save();
            $customer->setConfirmation(null);
            $customer->save();            
            Mage::getSingleton('customer/session')->loginById($customer->getId());
        }
        catch (Exception $ex) {
            echo $ex->getMessage();die;
        }
    }
	
	private function createFUser($_data){        
        $customer = Mage::getModel('customer/customer');
        $password = '';
        $email = $_data['email'];

        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());        
        $customer->setEmail($email);
        $customer->setFirstname($_data['first_name']);
        $customer->setLastname($_data['last_name']);
        $customer->setPassword($password);
        try {
            $customer->save();
            $customer->setConfirmation(null);
            $customer->save();            
            Mage::getSingleton('customer/session')->loginById($customer->getId());
        }
        catch (Exception $ex) {
            echo $ex->getMessage();die;
        }
    }

    private function createUser($_data,$email){		
        $customer = Mage::getModel('customer/customer');
        $password = '';
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->setEmail($email);
        $customer->setFirstname($_data['namePerson/friendly']);
        $customer->setLastname('');
        $customer->setPassword($password);
        try {
            $customer->save();
            $customer->setConfirmation(null);
            $customer->save();            
            Mage::getSingleton('customer/session')->loginById($customer->getId());
        }
        catch (Exception $ex) {
            echo $ex->getMessage();die;
        }
    }

    private function loginUser($_customer){        
            Mage::getSingleton('customer/session')->loginById($_customer->getId());
    }
}

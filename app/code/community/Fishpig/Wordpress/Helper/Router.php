<?php
/**
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Router extends Fishpig_Wordpress_Helper_Abstract
{
	/**
	 * The variable used for pages
	 *
	 * @var string
	 */
	protected $_postPagerVar = 'page';
	
	/**
	 * The variable format used for comment pages
	 *
	 * @var string
	 */
	protected $_commentPagerVarFormat = '^comment-page-%s$';
	
	/**
	 * The variable used to indicate this is a feed page
	 *
	 * @var string
	 */
	protected $_feedVar = 'feed';
	
	/**
	 * The variable used to indicate a trackback page
	 *
	 * @var string
	 */
	protected $_trackbackVar = 'trackback';
	
	/**
	 * Retrieve the blog URI
	 * This is the whole URI after blog route
	 *
	 * @return string
	 */
	public function getBlogUri()
	{
		$pathInfo = explode('/', strtolower(trim($this->getRequest()->getPathInfo(), '/')));
		
		if (count($pathInfo) == 0) {
			return null;
		}
		
		if ($pathInfo[0] != $this->getBlogRoute()) {
			return null;
		}

		// Remove blog route
		array_shift($pathInfo);
		
		// Clean off pager and feed parts
		if (($key = array_search($this->getPostPagerVar(), $pathInfo)) !== false) {
			if (isset($pathInfo[($key+1)]) && preg_match("/[0-9]{1,}/", $pathInfo[($key+1)])) {
				$this->getRequest()->setParam($this->getPostPagerVar(), $pathInfo[($key+1)]);
				unset($pathInfo[($key+1)]);
				unset($pathInfo[$key]);
				
				$pathInfo = array_values($pathInfo);
			}
		}
		
		// Clean off feed and trackback variable
		foreach(array($this->getFeedVar(), $this->getTrackbackVar()) as $var) {
			if (($key = array_search($var, $pathInfo)) !== false) {
				unset($pathInfo[$key]);
				$pathInfo = array_values($pathInfo);
				$this->getRequest()->setParam($var, 1);
			}
		}
		
		// Remove comments pager variable
		foreach($pathInfo as $i => $part) {
			$results = array();
			if (preg_match("/" . sprintf($this->getCommentPagerVarFormat(), '([0-9]{1,})') . "/", $part, $results)) {
				if (isset($results[1])) {
					unset($pathInfo[$i]);
				}
			}
		}
		
		if (count($pathInfo) == 1 && preg_match("/^[0-9]{1,8}$/", $pathInfo[0])) {
			$this->getRequest()->setParam(Mage::helper('wordpress/post')->getPostIdVar(), $pathInfo[0]);
			
			array_shift($pathInfo);
		}

		return urldecode(implode('/', $pathInfo));
	}
	
	/**
	 * Determine whether the URI is a blog category URI with no base
	 * Category URI's with a base are matched using a regular expression
	 * In the router (static route)
	 *
	 * @param string
	 * @return bool
	 */
	public function isNoBaseCategoryUri($uri)
	{
		if (!$this->categoryUrlHasBase()) {
			if (($terms = $this->getTermTaxonomyArray($uri)) !== false) {
				$uniqueTaxonomies = array_values(array_unique($terms));
				
				return count($uniqueTaxonomies) === 1 && $uniqueTaxonomies[0] === 'category';
			}
		}
		
		return false;
	}

	/**
	 * Determine whether the URI is a blog term URI
	 *
	 * @param string $uri
	 * @return bool
	 */
	public function isTermUri($uri)
	{
		if (strpos($uri, '/') === false) {
			return Mage::getResourceModel('wordpress/term')->slugExists($uri);
		}
		
		return false;
	}
	
	/**
	 * Determine whether the URI is a URI for a category/term page
	 *
	 * @param string $uri
	 * @return bool
	 */
	public function isCategoryTermUri($uri)
	{
		$uri = $this->trimCategoryBaseFromUri($uri);
		
		if (($terms = $this->getTermTaxonomyArray($uri)) !== false) {
			return array_pop($terms) !== 'category' && count(array_unique($terms)) === 1;
		}
		
		return false;
	}
	
	/**
	 * Retrieve an array containing each URI part as a key and
	 * it's taxonomy type as the value
	 * If the URI doesn't match exact terms (including parent relationships)
	 * false is returned
	 *
	 * @param string $uri
	 * @return array|false
	 */
	public function getTermTaxonomyArray($uri)
	{
		$key = 'taxonomy_array';
		
		if (!$this->_isCached($key)) {
			$this->_cache($key, Mage::getResourceModel('wordpress/term')->getTermTaxonomyArray(explode('/', $uri)));
		}
		
		return $this->_cached($key);
	}
	
	/**
	 * Determine whether the URI is a blog post URI
	 *
	 * @param string $uri
	 * @return bool
	 */
	public function isPostUri($uri)
	{
		return Mage::helper('wordpress/post')->isPostUri($uri);
	}

	/**
	 * Determine whether the URI is a blog post attachment URI
	 *
	 * @param string $uri
	 * @return bool
	 */
	public function isPostAttachmentUri($uri)
	{
		return Mage::helper('wordpress/post')->isPostAttachmentUri($uri);
	}
	
	/**
	 * Determine whether the URL is a page URI
	 *
	 * @param string $uri
	 * @return bool
	 */
	public function isPageUri($uri, $registerPage = true)
	{
		if (trim($uri) === '') {
			return false;
		}

		$uris = explode('/', $uri);
		$pages = array();
		$count = 0;
		
		foreach($uris as $uri) {
			$page = Mage::getModel('wordpress/page')->loadBySlug($uri);
			
			if (!$page->getId()) {
				return false;
			}
			
			if ($count++ > 0) {
				$lastPage = end($pages);
				$page->setParentPage($lastPage);
				reset($pages);
			}
			else {
				if ($page->getPostParent() > 0) {
					return false;
				}
			}
			
			$pages[] = $page;
		}
		
		if ($registerPage) {
			Mage::register('wordpress_page', array_pop($pages), true);
		}
		
		return true;
	}
	
	/**
	 * Trim the base from the URI
	 *
	 * @param string $uri
	 * @param string $base
	 * @param string $ltrim
	 * @return string
	 */
	public function trimUriBase($uri, $base, $ltrim = '/')
	{
		if (substr($uri, 0, strlen($base)) === $base) {
			$uri = substr($uri, strlen($base));
			
			if (!is_null($ltrim)) {
				$uri = ltrim($uri, $ltrim);
			}
		}
		
		return $uri;
	}
	
	/**
	 * Retrieve the URI with the base portion trimmed off
	 *
	 * @param string $base
	 * @param string $ltrim
	 * @return string
	 */
	public function getTrimmedUri($base, $ltrim = '/')
	{
		return $this->trimUriBase($this->getBlogUri(), $base, $ltrim);
	}

	/**
	 * Determine whether the category URL has a base
	 *
	 * @return bool
	 */
	public function categoryUrlHasBase()
	{
		$helper = Mage::helper('wordpress');
		
		return !($helper->isPluginEnabled('No Category Base WPML') || $helper->isPluginEnabled('No Category Base'));
	}
	
	/**
	 * Retrieve the category base
	 *
	 * @return string
	 */
	public function getCategoryBase()
	{
		return trim(Mage::helper('wordpress')->getWpOption('category_base', 'category'), '/');
	}
	
	/**
	 * Trim the category base from the URI
	 *
	 * @param string $uri
	 * @return string
	 */
	public function trimCategoryBaseFromUri($uri)
	{
		if ($this->categoryUrlHasBase()) {
			return $this->trimUriBase($uri, $this->getCategoryBase());
		}
		
		return $uri;
	}
	
	/**
	 * Retrieve the tag base
	 *
	 * @return string
	 */
	public function getTagBase()
	{
		$base = trim(Mage::helper('wordpress')->getWpOption('tag_base', 'tag'), '/');
		
		if (Mage::helper('wordpress')->isWordpressMU()) {
			$find = 'blog/';
			
			if (substr($base, 0, strlen($find)) == $find) {
				$base = substr($base, strlen($find));
			}
		}
		
		return $base;
	}
	
	/**
	 * Retrieve the Regex pattern used to identify a permalink string
	 * Allows for inclusion of other locale characters
	 *
	 * @return string
	 */
	public function getPermalinkStringRegex()
	{
		return '[a-z0-9' . $this->getSpecialUriChars() . '_\-\.]{1,}';
	}

	/**
	 * Retrieve an array of special chars that can be used in a URI
	 *
	 * @return array
	 */
	public function getSpecialUriChars()
	{
		$chars = array('‘', '’','“', '”', '–', '—', '`');
		
		if (Mage::helper('wordpress')->isCryllicLocaleEnabled()) {
			$chars[] = '\p{Cyrillic}';
		}
			
		return implode('', $chars);	
	}
	
	/**
	 * Retrieve the format variable for the comment pager
	 *
	 * @return string
	 */
	public function getCommentPagerVarFormat()
	{
		return $this->_commentPagerVarFormat;
	}
	
	/**
	 * Retrieve the post pager variable
	 *
	 * @return string
	 */
	public function getPostPagerVar()
	{
		return $this->_postPagerVar;
	}
	
	/**
	 * Retrieve the feed variable
	 *
	 * @return string
	 */
	public function getFeedVar()
	{
		return $this->_feedVar;
	}
	
	/**
	 * Retrieve the trackback variable
	 *
	 * @return string
	 */
	public function getTrackbackVar()
	{
		return $this->_trackbackVar;
	}
	
	/**
	 * Retrieve the request object
	 *
	 * @return
	 */
	public function getRequest()
	{
		return Mage::app()->getRequest();
	}
}

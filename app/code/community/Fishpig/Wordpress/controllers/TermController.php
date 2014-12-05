<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_TermController extends Fishpig_Wordpress_Controller_Abstract
{
	/**
	 * Used to do things en-masse
	 * eg. include canonical URL
	 *
	 * @return false|Fishpig_Wordpress_Model_Term
	 */
	public function getEntityObject()
	{
		return $this->_initTerm();
	}
	
	/**
	 * Ensure that the term loaded isn't a default term
	 * Default terms (post_category, tag etc) have their own controller
	 *
	 * @return $this|false
	 */
	public function preDispatch()
	{
		parent::preDispatch();
		
		$term = $this->_initTerm();
		
		if ($term->isDefaultTerm()) {
			$this->_forceForwardViaException('noRoute');
			return false;
		}
		

		return $this;	
	}
	
	/**
	  * Display the term page and list associated posts
	  *
	  */
	public function viewAction()
	{
		$term = Mage::registry('wordpress_term');
		
		$this->_addCustomLayoutHandles(array(
			'wordpress_term_index', 
		));
			
		$this->_initLayout();
		
		$this->_rootTemplates[] = 'template_post_list';

		if (($category = $this->_getCategory()) !== false) {
			if (($tree = $category->getTermTree()) !== false) {
				foreach($tree as $branch) {
					$this->_addCrumb('category_' . $branch->getId(), array('link' => $branch->getUrl(), 'label' => $branch->getName()));
					$this->_title($branch->getName());
				}
			}
		}
		
		$this->_title($term->getName());
		$this->_addCrumb('term', array('label' => $term->getName()));
		
		$this->renderLayout();
	}


	/**
	 * Initialise the term model
	 *
	 * @return false|Fishpig_Wordpress_Model_Term
	 */
	protected function _initTerm()
	{
		if (($term = Mage::registry('wordpress_term')) !== null) {
			return $term;
		}
		
		$term = Mage::getModel('wordpress/term')->loadBySlug($this->getRouterHelper()->getBlogUri());
			
		if ($term->getId()) {
			Mage::register('wordpress_term', $term);
			return $term;
		}

		return false;
	}
	
	/**
	 * Retrieve the WordPress category
	 * This can sometimes be set when a %category%/%term%
	 *
	 * @return false|Fishpig_Wordpress_Model_Post_Category
	 */
	protected function _getCategory()
	{
		if (($category = Mage::registry('wordpress_category')) !== null) {
			return $category;
		}
		
		return false;
	}
}

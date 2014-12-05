<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Post_CategoryController extends Fishpig_Wordpress_Controller_Abstract
{
	/**
	 * Used to do things en-masse
	 * eg. include canonical URL
	 *
	 * @return false|Fishpig_Wordpress_Model_Post_Category
	 */
	public function getEntityObject()
	{
		return $this->_initPostCategory();
	}
	
	/**
	 * If a term has been initiated in self_initPostCategory
	 * forward to wordpress/term/view action
	 *
	 * @return $this
	 */
	public function preDispatch()
	{
		parent::preDispatch();
		
		if (($term = Mage::registry('wordpress_term')) !== null) {
			$this->_forceForwardViaException('view', 'term');
			return false;
		}		
		
		return $this;
	}

	/**
	  * Display the category page and list blog posts
	  *
	  */
	public function viewAction()
	{
		$category = Mage::registry('wordpress_category');
		
		$this->_addCustomLayoutHandles(array(
			'wordpress_category_index', 
			'wordpress_category_'.$category->getId(),
			'WORDPRESS_CATEGORY_'.$category->getId(),
		));
			
		$this->_initLayout();
		
		$this->_rootTemplates[] = 'template_post_list';
		
		if (($tree = $category->getTermTree()) !== false) {
			$branches = count($tree);
			
			foreach($tree as $branch) {
				if (--$branches >= 1) {
					$this->_addCrumb('category_' . $branch->getId(), array('link' => $branch->getUrl(), 'label' => $branch->getName()));
				}
				else {
					$this->_addCrumb('category_' . $branch->getId(), array('label' => $branch->getName()));
				}

				$this->_title($branch->getName());
			}
		}
		
		$this->renderLayout();
	}

	
	/**
	 * Load the category based on the slug stored in the param 'category'
	 *
	 * @return Fishpig_Wordpress_Model_Post_Categpry
	 */
	protected function _initPostCategory()
	{
		$helper = Mage::helper('wordpress/router');
		
		$uri = $helper->trimCategoryBaseFromUri($helper->getBlogUri());

		$isCategoryTermUri = $helper->isCategoryTermUri($uri);
		
		if ($isCategoryTermUri) {
			if (($category = Mage::registry('wordpress_category')) !== null && !is_null(Mage::registry('wordpress_term'))) {
				return $category;
			}
		}
		else if (($category = Mage::registry('wordpress_category')) !== null) {
			return $category;
		}

		$termTaxonomies = $helper->getTermTaxonomyArray($uri);
		
		if ($isCategoryTermUri) {
			$flippedTermTaxonomies = array_flip($termTaxonomies);
			
			$term = Mage::getModel('wordpress/term')->loadBySlug(array_pop($flippedTermTaxonomies));
			
			if (!$term->getId() || $term->isDefaultTerm()) {
				return false;
			}
			
			Mage::register('wordpress_term', $term);
			
			array_pop($termTaxonomies);
		}

		$category = Mage::getModel('wordpress/post_category')->loadBySlugs(array_keys($termTaxonomies));

		if ($category->getId()) {
			Mage::register('wordpress_category', $category);
			
			if ($isCategoryTermUri) {
				Mage::registry('wordpress_term')->setCategory($category);
			}
			
			return $category;
		}

		return false;
	}
	
	/**
	 * Display the comment feed
	 *
	 */
	public function feedAction()
	{
		$this->getResponse()
			->setHeader('Content-Type', 'text/xml; charset=' . Mage::helper('wordpress')->getWpOption('blog_charset'), true)
			->setBody($this->getLayout()->createBlock('wordpress/feed_category')->setCategory(Mage::registry('wordpress_category'))->toHtml());
	}
}

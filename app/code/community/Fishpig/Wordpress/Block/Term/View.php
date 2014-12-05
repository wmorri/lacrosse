<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Term_View extends Fishpig_Wordpress_Block_Post_List_Wrapper_Abstract
{
	/**
	 * Returns the term usd for this block
	 *
	 * @return Fishpig_Wordpress_Model_Term
	 */
	public function getTerm()
	{
		if (!$this->hasTerm()) {
			$this->setTerm(false);
			
			if ($this->hasTermId()) {
				$term = Mage::getModel('wordpress/term')->load($this->getTermId());
				
				if ($term->getId()) {
					$this->setTerm($term);
				}
			}
			else if ($this->hasTermSlug()) {
				$term = Mage::getModel('wordpress/term')->loadBySlug($this->getTermSlug());
				
				if ($term->getId()) {
					$this->setTerm($term);
				}
			}
			else {
				$this->setTerm(Mage::registry('wordpress_term'));
			}
		}
		
		return $this->_getData('term');
	}

	/**
	 * Generates and returns the collection of posts
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */
	protected function _getPostCollection()
	{
		if ($this->getTerm()) {
			return $this->getTerm()->getPostCollection();
		}
		
		return false;
	}
}

<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Model_Resource_Term extends Fishpig_Wordpress_Model_Resource_Abstract
{
	public function _construct()
	{
		$this->_init('wordpress/term', 'term_id');
	}
	
	/**
	 * Custom load SQL to combine required tables
	 *
	 * @param string $field
	 * @param string|int $value
	 * @param Mage_Core_Model_Abstract $object
	 */
	protected function _getLoadSelect($field, $value, $object)
	{
		$select = $this->_getReadAdapter()->select()
			->from(array('main_table' => $this->getMainTable()));
		
		if (strpos($field, '.') !== false) {
			$select->where($field . '=?', $value);
		}
		else {
			$select->where("main_table.{$field}=?", $value);
		}
			
		$select->join(
			array('taxonomy' => $this->getTable('wordpress/term_taxonomy')),
			'`main_table`.`term_id` = `taxonomy`.`term_id`',
			array('term_taxonomy_id', 'taxonomy', 'description', 'count', 'parent')
		);
		
		if ($object->getTaxonomy()) {
			$select->where('taxonomy.taxonomy=?', $object->getTaxonomy());
		}

		return $select->limit(1);
	}
	
	/**
	 * Retrieve an array containing $slugs as keys
	 * and the related taxonomy type as value
	 *
	 * @param array $slugs
	 * @return array|false
	 */
	public function getTermTaxonomyArray(array $slugs)
	{
		$select = $this->_getReadAdapter()
			->select()
			->from(array('main_table' => $this->getMainTable()), array('slug'))
			->where('slug IN (?)', $slugs)
			->join(
				array('taxonomy' => $this->getTable('wordpress/term_taxonomy')),
				'`main_table`.`term_id` = `taxonomy`.`term_id`',
				'taxonomy'
			);
		
		if (($terms = $this->_getReadAdapter()->fetchAll($select)) !== false) {
			if (count($terms) === count($slugs)) {
				foreach($terms as $it => $term) {
					$terms[$term['slug']] = $term['taxonomy'];
					unset($terms[$it]);
				}
				
				foreach($slugs as $it => $slug) {
					$slugs[$slug] = $terms[$slug];
					unset($slugs[$it]);
				}
				
				return $slugs;
			}
		}
		
		return false;
	}
	
	/**
	 * Loads a category by an array of slugs
	 * The array should be the order of slugs found in the URI
	 * The whole slug array must match (including parent relationsips)
	 *
	 * @param array $slugs
	 * @param Fishpig_Wordpress_Model_Term $object
	 * @return false
	 */
	public function loadBySlugs(array $slugs, Fishpig_Wordpress_Model_Term $object)
	{
		$slugs = array_reverse($slugs);
		$primarySlug = array_shift($slugs);

		try {
			$object->loadBySlug($primarySlug);
			
			if ($object->getId()) {
				$category = $object;
				
				foreach($slugs as $slug) {
					$parent = Mage::getModel($object->getResourceName())->loadBySlug($slug);
					
					if ($parent->getId() !== $category->getParent()) {
						throw new Exception('This path just ain\'t right, bro!');
					}
					
					$category->setParentTerm($parent);
					$category = $parent;
				}

				if (!$category->getParentId()) {
					return true;
				}
			}
		}
		catch (Exception $e) {}
	
		$object->setData(array())->setId(null);

		return false;
	}
	
	/**
	 * Determine whether the taxonomy exists
	 *
	 * @param string $taxonomy
	 * @return bool
	 */
	public function taxonomyExists($taxonomy)
	{
		$select = $this->_getReadAdapter()
			->select()
			->from($this->getTable('wordpress/term_taxonomy'), 'term_taxonomy_id')
			->where('taxonomy=?', $taxonomy)
			->limit(1);
			
		return $this->_getReadAdapter()->fetchOne($select) !== false;
	}
	
	/**
	 * Determine whether the slug is a valid slug used by a term
	 *
	 * @param string $slug
	 * @return bool
	 */
	public function slugExists($slug)
	{
		$select = $this->_getReadAdapter()
			->select()
			->from($this->getMainTable(), 'term_id')
			->where('slug=?', $slug)
			->limit(1);
		
		return $this->_getReadAdapter()->fetchOne($select) !== false;
	}

	/**
	 * Retrieve a taxonomy for a post
	 *
	 * @param Fishpig_Wordpress_Model_Post|int $post
	 * @param string $taxonomy
	 * @return Fishpig_Wordpress_Model_Term
	 */	
	public function getTermByPostAndTaxonomy($post, $taxonomy)
	{
		if (is_object($post)) {
			$post = $post->getId();
		}
		
		$terms = 	Mage::getResourceModel('wordpress/term_collection')
			->addTaxonomyFilter($taxonomy)
			->addObjectIdFilter($post)
			->setPageSize(1)
			->setCurPage(1)
			->load();
		
		if (count($terms) > 0) {
			return $terms->getFirstItem();
		}
		
		return false;
	}
}

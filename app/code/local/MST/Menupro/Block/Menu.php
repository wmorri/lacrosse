<?php
/**
* Magento Support Team.
* @category   MST
* @package    MST_Menupro
* @version    2.0
* @author     Magebay Developer Team <info@magebay.com>
* @copyright  Copyright (c) 2009-2013 MAGEBAY.COM. (http://www.magebay.com)
*/
class MST_Menupro_Block_Menu extends Mage_Core_Block_Template {
	protected $_plus = '<span class="icon-plus expand"></span>';
	protected $_data_hover = "data-hover='dropdown'";
	protected $_dropdown_toggle = "class='dropdown-toggle'";
	protected $_rightIcon = '<i class="icon-chevron-right"></i>';
	protected $_urlValue;
	protected $_liClasses;
	protected $_aHref;
	protected $_aImage;
	protected $_aText;
	protected $_aIcon;
	protected $_aTarget;
	protected $_parent = false;
	protected $_block;
	// protected $_type;
	protected $_menuLink;
	// protected $_itemUrlValue;
	/**
	* If li has sub item, then we need to add some class such as: parent,
	* has-sub, etc... Need a space before or after each class
	*/
	// protected $_liHasSubClasses = 'dropdown parent ';
	protected $_liHasSubClasses = ' dropdown parent ';
	// A class of li that has a link being actived.
	protected $_liActiveClass = ' active ';
	// Column layout classes: sub_one, sub_two...
	protected $_columnLayout = array (
			1 => 'one',
			2 => 'two',
			3 => 'three',
			4 => 'four',
			5 => 'five',
			6 => 'six',
			100 => 'full' 
	);
	
	//---Auto Show Sub--
	protected $_tree = array();
	public $categoryObject;
	public function __construct()
	{
		/**
		 * Speed up connection ....
		 */
		$categoryTree = Mage::getSingleton('core/session')->getCategoryTree();
		$this->categoryObject = Mage::getSingleton("menupro/categories");
		//Save in session
		if (!$categoryTree) {
			$categories = $this->categoryObject->getCategories();
			foreach ($categories as $category)
			{
				$catData = $category->getData();
				//Sorted child 
				$allChild = $category->getChildrenCategories();
				$childString = "";
				if (count($allChild) > 0) {
					$child = array();
					foreach ($allChild as $cate) {
						$child[] = $cate->getData('entity_id');
					}
					//print_r($child);
					$childString = join(',' , $child);
				}
				$catData['children'] = $childString;
				$allCategories[$category->getEntityId()] = $catData;
			}
			Mage::getSingleton('core/session')->setCategoryTree($allCategories);
			$this->_tree = $allCategories;
		} else {
			$this->_tree = $categoryTree;
		}
		/* $this->categoryObject = Mage::getSingleton("menupro/categories");
		$categories = $this->categoryObject->getCategories();
		foreach ($categories as $category)
		{
			$catData = $category->getData();
			$catData['children'] = $category->getChildren();
			$this->_tree[$category->getEntityId()] = $catData;
		} */

	}
	
	public function getSortChildCollection($id)
	{
		$collection = Mage::getModel('catalog/category')
					->getCollection()
					->addAttributeToSelect('all_children')
					->addAttributeToFilter('entity_id', $id)
					->load();
		return $collection->toArray();
	}
	
	
	public function resetMenuItemVar() {
		$this->_liClasses = '';
		$this->_aHref = '';
		$this->_aImage = '';
		$this->_aText = '';
		$this->_aTarget = '';
		$this->_block = '';
		$this->_aIcon = '';
		$this->_parent = false;
	}
	
	public function getChildIds($tree, $id)
	{
		if (!array_key_exists($id, $tree)) {
			return;
		}
		$childIds = explode(',', $tree[$id]['children']);
		$showChildIds = array();
		foreach ($childIds as $childId) {
			if ($childId != "") {
				if (array_key_exists ($childId, $tree)) {
					$child = $tree[$childId];
					if ($child['include_in_menu'] == 1 && $child['is_active'] == 1) {
						$showChildIds[] = $childId;
					}
				}
			}
		}
		return $showChildIds;
	}
	
	public function getChildMenu($groupId, $menuId, $permission, $storeId) {
		return Mage::getModel ( 'menupro/menupro' )->getChildMenu ( $groupId, $menuId, $permission, $storeId );
	}
	/* public function getCategoriesById($itemUrlValue, $groupId, $menuId, $permission, $storeId) {
		$autosub = Mage::getModel ( "menupro/categories" )->getCategoriesById ( $itemUrlValue, $groupId, $menuId, $permission, $storeId );
		return $autosub;
	} */
	public function getMenuCollection($groupId, $permission, $storeId) {
		$isEnabled = Mage::getSingleton('menupro/groupmenu')->load($groupId)->getStatus();
		if ($isEnabled == 1) {
			return Mage::getModel ( "menupro/menupro" )->getMenuByGroupId ( $groupId, $permission, $storeId );
		}
		return;
	}
/* 	public function getMenuCollection($groupId, $permission, $storeId) {
		$isEnabled = Mage::getSingleton('menupro/groupmenu')->load($groupId)->getStatus();
		if ($isEnabled == 1) {
			$menuTree = Mage::getSingleton('core/session')->getMenuTreeCollection();
			if (!$menuTree) {
				$menuCollection = array();
				$menus = Mage::getModel ( "menupro/menupro" )->getMenuByGroupId ( $groupId, $permission, $storeId );
				foreach ($menus as $menu)
				{
					$menuData = $menu->getData();
					$menuCollection[$menu->getMenuId()] = $menuData;
				}
				Mage::getSingleton('core/session')->setMenuTreeCollection($menuCollection);
			}
			return Mage::getSingleton('core/session')->getMenuTreeCollection();
		}
		return;
	} */
	/**
	* @param $type as menu type: cms,block,category,custom @param $itemUrlValue
	* value of item input by user return Link of menu item
	*/
	public function getMenuLink($itemUrlValue, $type) {
		$store_id = Mage::app()->getStore()->getStoreId();
		$defaultStoreId = Mage::app()->getWebsite()->getDefaultGroup()->getDefaultStoreId();
		// Default store id = 11;
		// If current store is default, then remove store code from menu url
		switch ($type) {
			case 1 :
				if ($itemUrlValue == 'home') {
					if ($store_id == $defaultStoreId) {
						$this->_urlValue = Mage::getBaseUrl ( Mage_Core_Model_Store::URL_TYPE_WEB );
					} else {
						$this->_urlValue = Mage::getBaseUrl ( Mage_Core_Model_Store::URL_TYPE_LINK );
					}
				} else {
					$this->_urlValue = Mage::Helper ( 'cms/page' )->getPageUrl ( $itemUrlValue );
				}
				break;
			
			case 3 :
				if (strpos ( $itemUrlValue, 'http' ) === false) {
					if ($store_id == $defaultStoreId) {
						$this->_urlValue = Mage::getBaseUrl ( Mage_Core_Model_Store::URL_TYPE_WEB ) . $itemUrlValue;
					} else {
						$this->_urlValue = Mage::getBaseUrl ( Mage_Core_Model_Store::URL_TYPE_LINK ) . $itemUrlValue;
					}
				} else {
					$this->_urlValue = $itemUrlValue;
				}
				break;
			
			case 4 :
				$rootId = Mage::app()->getStore()->getRootCategoryId();
				if ($itemUrlValue == $rootId) {
					$this->_urlValue = "#";
				} else {
					$this->_urlValue = Mage::getModel('catalog/category')->load($itemUrlValue)->getUrl();
				}
				break;
			
			case 5 :
				$_product = Mage::getModel('catalog/product')->load($itemUrlValue);
				//$this->_urlValue = $_product->getProductUrl(true);// Still Working
				if ($store_id == $defaultStoreId) {
					$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
				} else {
					$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
				}
				$this->_urlValue = $baseUrl . $_product->getUrlPath();
				break;			
			case 6 :
				$this->_urlValue = Mage::getSingleton ( 'core/layout' )->createBlock ( 'cms/block' )->setBlockId ( $itemUrlValue );
				break;
			
			case 7 :
				$this->_urlValue = '#';
				break;
			
			default :
				$mostUsedUrl = Mage::helper('menupro')->getMostUsedUrl();
				$this->_urlValue = $mostUsedUrl[$itemUrlValue];
				break;
		}
		if ($type != 6) {
			//echo "<div style='display:none' class='testtest'><pre>" . print_r(Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)->getData()) . "</pre></div>";
			// Check current page is secure or not
			$validUrl = Mage::helper('menupro')->getValidUrl($this->_urlValue, Mage::app()->getStore()->isCurrentlySecure());
			$this->_urlValue = $validUrl;
		}	
		return $this->_urlValue;
	}
	public function getMenuData($menu, $permission, $storeId) {
		$this->_menuLink = $this->getMenuLink ( $menu->getUrlValue (), $menu->getType () );
		// Reset all val
		$this->resetMenuItemVar ();
		// Check has child or not
		$childCollection = $this->getChildMenu ( $menu->getGroupId (), $menu->getMenuId (), $permission, $storeId );
		if (count ( $childCollection ) > 0) {
			$this->_parent = true;
		}
		// Prepare classes for <li> tag. Level = 0
		$this->_liClasses .= $menu->getClassSubfix();
		if ($this->_menuLink == Mage::helper ( 'menupro' )->getCurrentUrl ()) {
			$this->_liClasses .= $this->_liActiveClass;
		}
		$isAutoShowSub = false;
		if ($menu->getAutosub() == 1) {
			$categoryHasChild = $this->getChildIds($this->_tree, $menu->getUrlValue());
			if (count($categoryHasChild) > 0) {
				$isAutoShowSub = true;
			}
		}
		if ($this->_parent == true || $isAutoShowSub) {
			//$groupMenuType = $this->getGroupMenuType ( $menu->getGroupId () );
			//$this->_liClasses .= $this->_liHasSubClasses[$groupMenuType];
			$this->_liClasses .= $this->_liHasSubClasses;
		}
		// Prepare for <a> tag. Check type is static block or not
		if ($menu->getType() != 6) {
			if ($menu->getImageStatus () == 1 && $menu->getImage () != "") {
				$image = Mage::getBaseUrl ( Mage_Core_Model_Store::URL_TYPE_MEDIA ) . '/' . $menu->getImage ();
				//$this->_aImage = "style='background-image:url(" . $image . ")'";
				$this->_aImage = "<img src='" . $image ."'  />";
				// $this->_aImage = "<img src='" . $image ."' width='22px'
				// height='22px' />";
			}
			$menu->getTarget () == 1 ? $a_target = "_self" : $a_target = "_blank";
			$this->_aHref = $this->_menuLink;
			
			//Use category title as default
			$menuTitle = $menu->getTitle();
			if ($menu->getType() == 4 && $menu->getUseCategoryTitle() == 2) {
				$menuTitle = Mage::helper('menupro')->getCategoryTitle($menu->getUrlValue());
			}
			
			if ($this->_menuLink == "#") {
				$this->_aText = "<span class='title'>" . $menuTitle . "</span>";
			} else {
				$this->_aText = "<span>" . $menuTitle . "</span>";
			}
			if($menu->getImageStatus() == 1){
				//$this->_aIcon = "<i class='" . $menu->getIconClass () . "' ". (($this->_aImage != '') ? $this->_aImage : '') ."></i>";
				$this->_aIcon = "<i class='" . $menu->getIconClass () . "'  >" . $this->_aImage . "</i>";
			}
		} else {
			$this->_block = $this->_menuLink->toHtml ();
		}
		return array (
			// 'menuLink' => $this->_menuLink,
			'liClasses' => $this->_liClasses,
			'aText' => $this->_aText,
			'aHref' => $this->_aHref,
			'target' => $this->_aTarget,
			'aImage' => '',//Just pass notice error
			'aIcon' => $this->_aIcon,
			'dropdown_columns' => $menu->getDropdownColumns (),
			'hide_sub_header' => $menu->getHideSubHeader (),
			'block' => $this->_block,
			'childcollection' => $childCollection,
			'autosub' => $menu->getAutosub(),
			'isAutoShowSub' => $isAutoShowSub
		);
	}
	//If menu don't have any sub
	public function getAutoSubMenuUl(
		$parentCategoryId, $autosub, 
		$childrenWrapClass = '', 
		$parentClass = 'parent dropdown has-submenu ', 
		$ulClass = 'dropdown-menu', 
		$aAttribute = 'data-hover="dropdown"', 
		$extraElement)
	{
		$html = "";
		if ($autosub == 1) {
			$subCollection = Mage::getModel('menupro/categories')->getChildCategoryCollection($parentCategoryId);
			if (count($subCollection) > 0) {
				/* $object = new MST_Menupro_Block_Autosub();
				$html .= "<ul class='" . $ulClass . "'>";//old class : dropdown-menu
				foreach($subCollection as $_category){
					$html .= $object->drawItem($_category, 0, $childrenWrapClass = '', $parentClass, $ulClass, $aAttribute, $extraElement);
				}
				$html .= "</ul>"; */
				$html .= "<ul class='" . $ulClass . "'>";//old class : dropdown-menu
				$html .= Mage::getModel('menupro/categories')->autoShowSubCategory($parentCategoryId);
				$html .= "</ul>";
			}
		}	
		return $html;
	}
	
	public function autoSub($categoryId, $autoShowSub, $showMenuInLevel2 = false)
	{
		//$categoryTree = Mage::getSingleton('core/session')->getCategoryTree();
		/* $html = $this->categoryObject->autoSub($categoryId, $this->_tree, $autoShowSub);
		return $html; */
		$html = $this->categoryObject->autoSub($categoryId, $this->_tree, $autoShowSub, $showMenuInLevel2);
		return $html; 
	}
	
	public function autoSubResponsive($categoryId, $autoShowSub)
	{
		//$categoryTree = Mage::getSingleton('core/session')->getCategoryTree();
		/* $html = $this->categoryObject->autoSubResponsive($categoryId, $this->_tree, $autoShowSub);
		return $html; */
		$html = $this->categoryObject->autoSubResponsive($categoryId, $this->_tree, $autoShowSub);
		return $html;
	}
	
	//If menu have sub
	public function getAutoSubMenuLi(
		$parentCategoryId, $autosub, 
		$childrenWrapClass = '', 
		$parentClass = 'parent dropdown has-submenu ', 
		$ulClass = 'dropdown-menu', 
		$aAttribute = 'data-hover="dropdown"', 
		$extraElement = '')
	{
		$html = "";
		if ($autosub == 1) {
			$subCollection = Mage::getModel('menupro/categories')->getChildCategoryCollection($parentCategoryId);
			if (count($subCollection) > 0) {
				/* $object = new MST_Menupro_Block_Autosub();
				foreach($subCollection as $_category){
					$html .= $object->drawItem($_category, 0, $childrenWrapClass = '', $parentClass, $ulClass, $aAttribute, $extraElement);
				} */
				$html .= Mage::getModel('menupro/categories')->autoShowSubCategory($parentCategoryId);
			}
		}
		return $html;
	}
	
	//Accoridion ------------------
	public function getAccoridionAutoSubMenuUl($parentCategoryId, $autosub)
	{
		$html = $this->getAutoSubMenuUl(
			$parentCategoryId, $autosub, 
			'', 
			'parent dropdown ', 
			'dropdown-menu', 
			'class="dropdown-toggle"', 
			'<span class="icon-plus expand"></span>'
		);
		return $html;
	}
	//If menu have sub
	public function getAccoridionAutoSubMenuLi($parentCategoryId, $autosub)
	{
		$html = $this->getAutoSubMenuLi(
			$parentCategoryId, $autosub,
			'', 
			'parent dropdown ', 
			'dropdown-menu', 
			'class="dropdown-toggle"', 
			'<span class="icon-plus expand"></span>'
		);
		return $html;
	}
	
	/**
	* Only allow block (type = 6) show in level 2 only.
	* @param groupId, menuId, permission, storeid
	* @return array
	*/
	public function getNormalType($groupId, $menuId, $permission, $storeId)
	{
		$childMenu = $this->getChildMenu($groupId, $menuId, $permission, $storeId); 
		$normalArray = null;
		foreach ($childMenu as $menuItem) {
			if ($menuItem->getType() != 6)	{
				$normalArray [] = $menuItem->getMenuId();
			}								
		}
		return $normalArray;
	}
}

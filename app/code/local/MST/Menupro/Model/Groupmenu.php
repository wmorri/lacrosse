<?php
class MST_Menupro_Model_Groupmenu extends Mage_Core_Model_Abstract {
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'menupro/groupmenu' );
	}
	public function getGroupArray() {
		$groupData = array ();
		$group_collection = Mage::getModel ( 'menupro/groupmenu' )->getCollection ();
		$group_collection->addFieldToFilter ( 'status', 1 );
		foreach ( $group_collection as $group ) {
			// $groupNames [$gname->getGroupId ()] = $gname->getTitle ();
			$groupData [] = array (
					'value' => $group->getGroupId (),
					'label' => $group->getTitle (),
					'menu_type' => $group->getMenuType () 
			);
		}
		return $groupData;
	}
	public function getAllGroupArray() {
		$groupData = array ();
		$group_collection = Mage::getModel ( 'menupro/groupmenu' )->getCollection ();
		//$group_collection->addFieldToFilter ( 'status', 1 );
		foreach ( $group_collection as $group ) {
			// $groupNames [$gname->getGroupId ()] = $gname->getTitle ();
			$groupData [] = array (
					'value' => $group->getGroupId (),
					'label' => $group->getTitle (),
					'menu_type' => $group->getMenuType () 
			);
		}
		return $groupData;
	}
	public function getOptionArray() {
		$arr_status = array (
				array (
						'value' => 1,
						'label' => Mage::helper ( 'menupro' )->__ ( 'Enabled' ) 
				),
				array (
						'value' => 2,
						'label' => Mage::helper ( 'menupro' )->__ ( 'Disabled' ) 
				) 
		);
		
		return $arr_status;
	}
	public function getMenuTypes() {
		return array (
				array (
						'value' => '',
						'label' => '--Please Select--' 
				),
				array (
						'value' => 'dropdown',
						'label' => 'Dropdown' 
				)
				/* array (
						'value' => 'dropline',
						'label' => 'Dropline' 
				) */
				,
				array (
						'value' => 'sidebar',
						'label' => 'Sidebar' 
				),
				array (
						'value' => 'accordion',
						'label' => 'Accordion' 
				) 
		);
	}
	public function installGuide($groupType, $groupId)
	{
		$html = '1, To embed Menu Group in CMS/Static Block: <br/><br/>{{block type="menupro/menu" name="menupro_' . $groupType.'" group_id="' . $groupId . '" template="menupro/' . $groupType . '.phtml" }}<br/><br/>';
		$html .= '2, To reference in custom xml:<br/><br/>';
		$html .= '<block type="menupro/menu" name="menupro_' . $groupType . '" ifconfig="menupro/setting/enable" template="menupro/' . $groupType . '.phtml"><br/>';
		$html .= '    <action method="setData"><name>group_id</name><value>' . $groupId . '</value></action><br/>';
		$html .= '</block>';
		return $html;
	}
}
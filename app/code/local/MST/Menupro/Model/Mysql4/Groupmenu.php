<?php
class MST_Menupro_Model_Mysql4_Groupmenu extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        // Note that the menu_id refers to the key field in your database table.
        $this->_init('menupro/groupmenu', 'group_id');
    }

}
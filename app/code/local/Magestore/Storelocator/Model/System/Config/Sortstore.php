<?php

class Magestore_Storelocator_Model_System_Config_Sortstore {

    public function toOptionArray() {
        $options = array(
            array('value' => 'distance', 'label' => Mage::helper('storelocator')->__('Distance')),
            array('value' => 'alphabeta', 'label' => Mage::helper('storelocator')->__('Alphabetical order')),
        );
        return $options;
    }

}
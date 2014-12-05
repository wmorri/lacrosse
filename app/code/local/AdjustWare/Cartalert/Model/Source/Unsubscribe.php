<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ mspEoSaPcPBwepZU('a66ef1a7db859fe3221313ef733438c9');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Model_Source_Unsubscribe extends Varien_Object
{
    public function toOptionArray()
    {
        $vals = array(
            0 => Mage::helper('adjcartalert')->__('Delete all pending alerts for current client'),
            1 => Mage::helper('adjcartalert')->__('Delete all pending alerts and store his email in \'Stop\' list for current store'),
            2 => Mage::helper('adjcartalert')->__('Delete all pending alerts and store his email in \'Stop\' list for all stores'),
            3 => Mage::helper('adjcartalert')->__('Allow clients to select an action'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array(
                    'value' => $k,
                    'label' => $v
            );
        
        return $options;
    }
} } 
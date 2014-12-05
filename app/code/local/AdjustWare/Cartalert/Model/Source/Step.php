<?php
if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ gshWqSkOUOjoDhrR('50ccb4cda777575db8c4899cc4f6f5c5');
/**
 * Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 18/06/13
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     GyXtOOgQTiFMnacYzvNJEFeF580kUqyHTVUnVH7mz8
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Model_Source_Step extends Varien_Object
{
    public function toOptionArray()
    {
        $options = array(
            0 => array(
                'value' => '',
                'label' => '-'
            )
        );

        foreach (array('first','second','third') as $step)
            $options[] = array(
                'value'=> $step,
                'label' => Mage::helper('adjcartalert')->__(ucfirst($step). ' Email Template')
            );
        
        return $options;
    }
} } 
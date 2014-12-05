<?php

$installer = $this;

$installer->startSetup();


$attributeInstaller = new Mage_Eav_Model_Entity_Setup('core_setup');
$attributeInstaller->addAttribute('catalog_product', 'smd_colorswatch_product_list', array(
    'group'                    => 'ColorSwatch',
    'type'                     => 'int',
    'input'                    => 'select',
    'label'                    => 'Show attribute on Product list page',
    'global'                   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'                  => 0,
    'required'                 => 0,
    'visible_on_front'         => 1,
    'is_html_allowed_on_front' => 0,
    'is_configurable'          => 0,
    'source'                   => 'eav/entity_attribute_source_boolean',
    'searchable'               => 0,
    'filterable'               => 0,
    'comparable'               => 0,
    'default'                   => 1,
    'unique'                   => false,
    'user_defined'             => false,
    'is_user_defined'          => false,
    'used_in_product_listing'  => 1
    
));
$attributeInstaller->updateAttribute('catalog_product', 'smd_colorswatch_product_list', 'apply_to', join(',', array('configurable')));
$attributeInstaller->updateAttribute('catalog_product', 'smd_colorswatch_product_list', 'used_in_product_listing', 1);

$installer->endSetup(); 
<?php
    /**
    * EcommerceTeam.com
    *
    * Seo Layered Navigation
    *
    * @category     Magento Extension
    * @copyright    Copyright (c) 2011 Ecommerce Team (http://www.ecommerce-team.com)
    * @author       Ecommerce Team
    * @version      3.0
    */

$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('ecommerceteam_sln_attribute_options')}` (
  `attribute_id` smallint(5) unsigned NOT NULL,
  `option_id` int(10) unsigned NOT NULL,
  `url_key` varchar(128) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY  (`option_id`),
  CONSTRAINT `FK_ecommerceteam_sln_attribute_options` FOREIGN KEY (`option_id`) REFERENCES `{$this->getTable('eav_attribute_option')}` (`option_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('ecommerceteam_sln_attribute_data')}` (
  `attribute_id` smallint(5) unsigned NOT NULL,
  `group_id` varchar(32) NOT NULL DEFAULT 'default',
  `frontend_type` varchar(128) NOT NULL DEFAULT 'default',
  `comment` varchar(255),
  PRIMARY KEY (`attribute_id`),
  CONSTRAINT `FK_ecommerceteam_sln_attribute_data` FOREIGN KEY (`attribute_id`) REFERENCES `{$this->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");
$installer->endSetup();

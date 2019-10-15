<?php

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('marketplace_shippinglist')}`(
  `mpshipping_id` int(11) NOT NULL AUTO_INCREMENT,
  `website_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Website Id',
  `dest_country_id` varchar(4) NOT NULL DEFAULT '0' COMMENT 'Destination coutry ISO/2 or ISO/3 code',
  `dest_region_id` varchar(255) NOT NULL DEFAULT '0' COMMENT 'Destination Region Id',
  `dest_zip` varchar(255) NOT NULL COMMENT 'Destination Post Code (Zip)',
  `dest_zip_to` varchar(255) NOT NULL DEFAULT '0' 'Destination Post Code (Zip)',
  `price` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Price',
  `weight_from` decimal(12,4) NOT NULL,
  `weight_to` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Cost',
  `partner_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mpshipping_id`),
  UNIQUE(`website_id`,`dest_country_id`,`dest_region_id`,`dest_zip`,`dest_zip_to`,`weight_from`,`weight_to`,`partner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('marketplace_tracking_number')}` (
  `tracking_id` int(11) NOT NULL auto_increment,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `item_ids` varchar(2048) NOT NULL DEFAULT '',
  `seller_id` int(11) NOT NULL DEFAULT '0',
  `shipping_charges` float(12,4) NOT NULL,
  `carrier_name` varchar(255) NOT NULL,
  `tracking_number` varchar(255) NOT NULL,
  PRIMARY KEY (`tracking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
    
 //Add new column to the 'saleslist' table
$installer->getConnection()->addColumn(
        $this->getTable('marketplace_saleslist'), //table name
        'shipping_charges',      //column name
        'decimal(12,4) NOT NULL'  //datatype definition
        );

$installer->endSetup(); 

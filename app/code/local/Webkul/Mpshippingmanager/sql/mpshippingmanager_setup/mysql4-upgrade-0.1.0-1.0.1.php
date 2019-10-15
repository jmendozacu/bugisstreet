<?php
$installer = $this;
$installer->startSetup();
$installer->run("
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

$connection = $this->getConnection();
$prefix = Mage::getConfig()->getTablePrefix();
$connection->addColumn($prefix.'marketplace_userdata', 'others_info', 'text NOT NULL');
$connection->addColumn($prefix.'marketplace_tracking_number', 'shipment_id', 'int(11) NOT NULL');
$connection->addColumn($prefix.'marketplace_tracking_number', 'invoice_id', 'int(11) NOT NULL');

$installer->endSetup();
	 

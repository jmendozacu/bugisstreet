<?php
$installer = $this;
$installer->startSetup();
$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('wk_mprma_new')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `orderid` int(11) NOT NULL,
  `itemid` int(11) NOT NULL,
  `sellerid` int(11) NOT NULL,
  `customerid` int(11) NOT NULL,
  `additionalinfo` text NOT NULL,
  `reason` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `consignment_no` varchar(255) NOT NULL,
  `customerstatus` int(11) NOT NULL,
  `sellerstatus` int(11) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('wk_mprma_reason')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `reason` text NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('wk_mprma_conversation')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rmaid` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` varchar(255) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `sendertype` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

$installer->endSetup();
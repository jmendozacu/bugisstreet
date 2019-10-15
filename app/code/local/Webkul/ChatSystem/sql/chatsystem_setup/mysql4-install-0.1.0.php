<?php
$installer = $this;
$installer->startSetup();
$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('wk_cs_conversation')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fromname` varchar(35) NOT NULL,
  `forid` int(11) NOT NULL,
  `toname` varchar(255) NOT NULL,
  `toid` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` bigint(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('wk_cs_user_chat_notify')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `touserid` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;");

$installer->endSetup();

?>
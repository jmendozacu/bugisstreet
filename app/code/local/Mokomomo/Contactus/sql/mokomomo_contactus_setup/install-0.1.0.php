<?php
echo 'Running This Upgrade: '.get_class($this)."\n <br /> \n";

echo 'Module: Mokomomo_Contactus / mokomomo_contactus <br />';

$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('contactus/contactus')}` (
        `contactus_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `field_name` VARCHAR(255) NOT NULL DEFAULT '',
        `match_value` VARCHAR(255) NOT NULL DEFAULT '',
        `email` VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY  (`contactus_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

<?php
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('marketplace/userprofile'),
        'areamobile',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => true,
            'comment' => 'Area Mobile'
        )
    );
$installer->getConnection()
    ->addColumn($installer->getTable('marketplace/userprofile'),
        'countrymobile',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'unsigned' => true,
            'nullable' => true,
            'comment' => 'Country Mobile'
        )
    );

$installer->endSetup();
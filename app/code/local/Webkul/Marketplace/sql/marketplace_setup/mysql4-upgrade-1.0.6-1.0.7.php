<?php
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('mprmasystem/rmarequest'),
        'updated_date',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'unsigned' => true,
            'nullable' => true,
            'comment' => 'Updated Date'
        )
    );
$installer->endSetup();
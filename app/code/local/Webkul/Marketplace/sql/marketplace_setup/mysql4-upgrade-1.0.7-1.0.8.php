<?php
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('mprmasystem/rmarequest'),
        'seller_update',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'unsigned' => true,
            'nullable' => true,
            'comment' => 'Seller Update'
        )
    );
$installer->endSetup();
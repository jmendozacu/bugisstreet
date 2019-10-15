<?php
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('marketplace/feedback'),
        'product_id',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => true,
            'comment' => 'Order ID'
        )
    );
$installer->getConnection()
    ->addColumn($installer->getTable('marketplace/feedback'),
        'order_id',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => true,
            'comment' => 'Product ID'
        )
    );

$installer->endSetup();
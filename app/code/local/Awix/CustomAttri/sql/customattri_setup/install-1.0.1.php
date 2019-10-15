<?php
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('customer/customer_group'),
        'member_type',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => true,
            'comment' => 'Membership Type'
        )
    );

$installer->getConnection()
    ->addColumn($installer->getTable('customer/customer_group'),
        'product_listing',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => true,
            'comment' => 'PL'
        )
    );
$installer->getConnection()
    ->addColumn($installer->getTable('customer/customer_group'),
        'transaction_percent',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => true,
            'comment' => 'TP'
        )
    );

$installer->endSetup();
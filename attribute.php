<?php require_once('app/Mage.php');
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
$installer = new Mage_Sales_Model_Mysql4_Setup;
$attribute  = array(
'type'          => 'smallint',
'backend_type'  => 'smallint',
'frontend_input' => 'smallint',
'is_user_defined' => true,
'label'         => 'Is Reminded',
'visible'       => true,
'required'      => false,
'user_defined'  => false,
'searchable'    => false,
'filterable'    => false,
'comparable'    => false,
'default'       => '0'
);
$installer->addAttribute('order', 'is_reminded', $attribute);
$installer->addAttribute('quote', 'is_reminded', $attribute);
$installer->endSetup();

echo "success";
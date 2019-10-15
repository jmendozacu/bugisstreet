<?php
/*$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table tablename(tablename_id int not null auto_increment, name varchar(100), primary key(tablename_id));
    insert into tablename values(1,'tablename1');
    insert into tablename values(2,'tablename2');
		
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
*/


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$attrCode = 'commission_for_product';
$attrGroupName = 'Prices';
$attrLabel = 'Commission For Product';
$attrNote = 'Commission';

$objCatalogEavSetup = Mage::getResourceModel('catalog/eav_mysql4_setup', 'core_setup');
$attrIdTest = $objCatalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, $attrCode);

if ($attrIdTest === false) {
    $objCatalogEavSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
        'group' => $attrGroupName,
        'sort_order' => 7,
        'type' => 'varchar',
        'backend' => '',
        'frontend' => '',
        'label' => $attrLabel,
        'note' => $attrNote,
        'input' => 'text',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        //'default' => '0',
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => true
    ));
}

$installer->endSetup();
	 

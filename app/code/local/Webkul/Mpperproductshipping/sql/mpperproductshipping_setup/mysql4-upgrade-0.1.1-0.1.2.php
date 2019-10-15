<?php
$installer = $this;
$installer->startSetup();

$objCatalogEavSetup = Mage::getResourceModel('catalog/eav_mysql4_setup', 'core_setup');

$attrCode = 'mp_shipping_allow_product';
$attrGroupName = 'Prices';
$attrLabel = 'Custom Shipping Allow';
$attrNote = 'Custom Shipping Allow for this product';
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
        'default' => 'Custom shipping 1',
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => true
    ));
}

$attrCode = 'mp_shipping_charge_cus_title';
$attrGroupName = 'Prices';
$attrLabel = 'Custom Shipping Charges Title';
$attrNote = 'Custom Shipping Charges Title for this product';
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
        'default' => 'Custom shipping 1',
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => true
    ));
}

$installer->endSetup();
	 

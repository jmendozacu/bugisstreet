<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("catalog_category", "commission_for_admin",  array(
    "type"     => "text",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Commission For Admin",
    "input"    => "textarea",
    "class"    => "validate-zero-or-greater",
    "source"   => "",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => "Commission that goes to "

	));
$installer->endSetup();
	 
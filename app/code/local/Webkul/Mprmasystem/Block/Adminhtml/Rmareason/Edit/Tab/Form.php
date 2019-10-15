<?php
	class Webkul_Mprmasystem_Block_Adminhtml_Rmareason_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

	    protected function _prepareForm() {
	        $form = new Varien_Data_Form();
	        $this->setForm($form);
	        $fieldset = $form->addFieldset("reason_form", array("legend" => Mage::helper("mprmasystem")->__("RMA Reason")));

			$fieldset->addField("reason", "textarea", array(
				"label"     =>  Mage::helper("mprmasystem")->__("Reason Provided"),
				"name"      =>  "reason",
				"class"     =>  "required-entry",
				"required"  =>  true
			));
			
			$fieldset->addField("status", "select", array(
				"label"     =>  Mage::helper("mprmasystem")->__("Status (ADMIN)"),
				"name" 		=>  "status",
				"class"     =>  "required-entry",
				"required"  =>  true,
				"values"	=>  array(
									array("value" => "","label" => Mage::helper("mprmasystem")->__("Please Select")),
									array("value" => "1","label" => Mage::helper("mprmasystem")->__("Enable")),
									array("value" => "0","label" => Mage::helper("mprmasystem")->__("Disable"))									
								)
			));
			
	        if(Mage::registry("mprmareason_data"))
	            $form->setValues(Mage::registry("mprmareason_data")->getData());			
	        return parent::_prepareForm();
	    }

	}
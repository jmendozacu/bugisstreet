<?php
	class Webkul_Mprmasystem_Block_Adminhtml_Allrma_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

	    protected function _prepareForm() {
	        $form = new Varien_Data_Form();
	        $this->setForm($form);
	        $fieldset = $form->addFieldset("mprma_form", array("legend" => Mage::helper("mprmasystem")->__("RMA Details")));

	        $fieldset->addField("incrementid", "text", array(
				"label"     =>  Mage::helper("mprmasystem")->__("Order Id"),
				"name"      =>  "incrementid",
				"style"		=>  "border:none;background:transparent;",
				"disabled"	=>  "disabled"
			));

			$fieldset->addField("name", "text", array(
				"label"     =>  Mage::helper("mprmasystem")->__("Customer Name"),
				"name"      =>  "name",
				"style"		=>  "border:none;background:transparent;",
				"disabled"	=>  "disabled"
			));

			$fieldset->addField("reason", "textarea", array(
				"label"     =>  Mage::helper("mprmasystem")->__("Reason Provided"),
				"name"      =>  "reason",
				"style"		=>  "border:none;background:transparent;resize:none;width:400px;",
				"disabled"	=>  "disabled"
			));

			$fieldset->addField("additionalinfo", "textarea", array(
				"label"     =>  Mage::helper("mprmasystem")->__("Additional Information"),
				"name"      =>  "additionalinfo",
				"style"		=>  "border:none;background:transparent;resize:none;width:400px;",
				"disabled"	=>  "disabled"
			));

			$fieldset->addField("status", "text", array(
				"label"     =>  Mage::helper("mprmasystem")->__("RMA Status"),
				"name"      =>  "status",
				"style"		=>  "border:none;background:transparent;",
				"disabled"	=>  "disabled"
			));

			$fieldset->addField("customerstatus", "text", array(
				"label"     =>  Mage::helper("mprmasystem")->__("Status (CUSTOMER)"),
				"name"      =>  "customerstatus",
				"style"		=>  "border:none;background:transparent;",
				"disabled"	=>  "disabled"
			));

			$fieldset->addField("consignment_no", "text", array(
				"label"     =>  Mage::helper("mprmasystem")->__("Consignment Number"),
				"name"      =>  "consignment_no",
				"style"		=>  "border:none;background:transparent;",
				"disabled"	=>  "disabled"
			));
			
			$fieldset->addField("sellerstatus", "select", array(
				"label"     =>  Mage::helper("mprmasystem")->__("Status (ADMIN)"),
				"name" 		=>  "sellerstatus",
                "class"     =>  "validate-select required-entry",
				"values"	=>  array(
									/*array("value" => 0,"label" => Mage::helper("mprmasystem")->__("Not Receive Package yet")),
									array("value" => 1,"label" => Mage::helper("mprmasystem")->__("Received Package")),
									array("value" => 2,"label" => Mage::helper("mprmasystem")->__("Dispatched Package")),
									array("value" => 3,"label" => Mage::helper("mprmasystem")->__("Declined/Returned"))*/

                                    array("value" => "","label" => Mage::helper("mprmasystem")->__("Please Select")),
									array("value" => 1,"label" => Mage::helper("mprmasystem")->__("Item Dispatched (Exchanged)")),
									array("value" => 2,"label" => Mage::helper("mprmasystem")->__("Refunded")),
									array("value" => 3,"label" => Mage::helper("mprmasystem")->__("Declined"))
								)
			));

			$fieldset->addField("convmessage", "textarea", array(
				"label"     => Mage::helper("mprmasystem")->__("Message"),
				"name"      => "convmessage",
				"style"		=> "resize:none;width:400px;"
			));
			
	        if(Mage::registry("mprma_data"))
	            $form->setValues(Mage::registry("mprma_data")->getData());
	        return parent::_prepareForm();
	    }

	}
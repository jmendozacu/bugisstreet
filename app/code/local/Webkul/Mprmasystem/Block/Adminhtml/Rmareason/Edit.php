<?php
    class Webkul_Mprmasystem_Block_Adminhtml_Rmareason_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

        public function __construct() {
            parent::__construct();
            $this->_objectId    = "id";
            $this->_blockGroup  = "mprmasystem";
            $this->_controller  = "adminhtml_rmareason";
            $this->_updateButton("back", "onclick", "back()");
            $this->_updateButton("delete", "onclick", "deletereason()");
            $this->_addButton("saveandcontinue", array(
                "label"     => Mage::helper("adminhtml")->__("Save And Continue Edit"),
                "onclick"   => "saveAndContinueEdit()",
                "class"     => "save",
                    ), -100);
            $this->_formScripts[] = "function saveAndContinueEdit(){
                                        editForm.submit($('edit_form').action+'back/edit/');
                                    }
                                    function back(){
                                        setLocation('".$this->getUrl('*/*/reason')."');
                                    }
                                    function deletereason(){
                                        setLocation('".$this->getUrl('*/*/deletereason',array("id"=>Mage::registry("mprmareason_data")->getId()))."');
                                    }";
        }

        public function getHeaderText() {
            return Mage::helper("mprmasystem")->__("View Reason id").$this->htmlEscape(Mage::registry("mprmareason_data")->getId());
        }

    }
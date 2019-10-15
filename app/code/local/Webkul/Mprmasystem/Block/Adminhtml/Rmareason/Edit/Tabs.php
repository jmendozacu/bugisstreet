<?php
    class Webkul_Mprmasystem_Block_Adminhtml_Rmareason_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

        public function __construct() {
            parent::__construct();
            $this->setId("rmasystem_tabs");
            $this->setDestElementId("edit_form");
            $this->setTitle(Mage::helper("mprmasystem")->__("Add/Edit Reason"));
        }

        protected function _beforeToHtml() {
            $this->addTab("form_section", array(
                "label"     => Mage::helper("mprmasystem")->__("RMA Reason"),
                "alt"       => Mage::helper("mprmasystem")->__("RMA Reason"),
                "content"   => $this->getLayout()->createBlock("mprmasystem/adminhtml_rmareason_edit_tab_form")->toHtml()
            ));
            return parent::_beforeToHtml();
        }

    }
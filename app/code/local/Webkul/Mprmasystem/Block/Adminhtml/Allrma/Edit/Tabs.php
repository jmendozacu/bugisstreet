<?php
    class Webkul_mprmasystem_Block_Adminhtml_Allrma_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

        public function __construct() {
            parent::__construct();
            $this->setId("mprmasystem_tabs");
            $this->setDestElementId("edit_form");
            $this->setTitle(Mage::helper("mprmasystem")->__("RMA Information"));
        }

        protected function _beforeToHtml() {
            $this->addTab("form_section", array(
                "label"     => Mage::helper("mprmasystem")->__("RMA Details"),
                "alt"       => Mage::helper("mprmasystem")->__("RMA Details"),
                "content"   => $this->getLayout()->createBlock("mprmasystem/adminhtml_allrma_edit_tab_form")->toHtml()
            ));
            $this->addTab("conversation_section", array(
                "label"     => Mage::helper("mprmasystem")->__("Conversation"),
                "alt"       => Mage::helper("mprmasystem")->__("Conversation"),
                "content"   => $this->getLayout()->createBlock("mprmasystem/adminhtml_allrma_edit_tab_conversation")->toHtml()
            ));
            return parent::_beforeToHtml();
        }

    }
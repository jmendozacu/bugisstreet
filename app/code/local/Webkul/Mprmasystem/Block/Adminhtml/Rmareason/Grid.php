<?php 
    class Webkul_Mprmasystem_Block_Adminhtml_Rmareason_Grid extends Mage_Adminhtml_Block_Widget_Grid {

        public function __construct() {
            parent::__construct();
            $this->setId("rmareasongrid");
            $this->setUseAjax(true);
            $this->setSaveParametersInSession(true);
        }

        protected function _prepareCollection() {
            $collection = Mage::getModel("mprmasystem/rmareason")->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        protected function _prepareColumns() {

            $this->addColumn("id", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Id"),
                "align"     =>  "center",
                "width"     =>  "200px",
                "index"     =>  "id"
            ));

            $this->addColumn("reason", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Reason"),
                "align"     =>  "left",
                "index"     =>  "reason"
            ));

            $this->addColumn("status", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Status"),
                "align"     =>  "center",
                "type"      =>  "options",
                "align"     =>  "center",
                "width"     =>  "200px",
                "index"     =>  "status",
                "options"   =>  array("0" => Mage::helper("mprmasystem")->__("Disabled"), "1" => Mage::helper("mprmasystem")->__("Enabled"))
            ));

            $this->addExportType("*/*/exportCsvreason", Mage::helper("mprmasystem")->__("CSV"));
            $this->addExportType("*/*/exportXmlreason", Mage::helper("mprmasystem")->__("XML"));
            return parent::_prepareColumns();
        }

        protected function _prepareMassaction() {
            $this->setMassactionIdField("id");
            $this->getMassactionBlock()->setFormFieldName("ids");
            $this->getMassactionBlock()->addItem("delete", array(
                "label"      => Mage::helper("mprmasystem")->__("Delete"),
                "url"        => $this->getUrl("*/*/massDeletereason"),
                "confirm"    => Mage::helper("mprmasystem")->__("Are you sure")."?"
            ));
            $this->getMassactionBlock()->addItem("status", array(
                "label"      => Mage::helper("mprmasystem")->__("Change status"),
                "url"        => $this->getUrl("*/*/massStatusreason", array("_current" => true)),
                "additional" => array("visibility" => array(
                                                        "name"   => "status",
                                                        "type"   => "select",
                                                        "class"  => "required-entry",
                                                        "label"  => Mage::helper("mprmasystem")->__("Status"),
                                                        "values" => array("0" => Mage::helper("mprmasystem")->__("Disabled"), "1" => Mage::helper("mprmasystem")->__("Enabled"))
                                                    )
                                )
            ));
            return $this;
        }

        public function getRowUrl($row) {
            return $this->getUrl("*/*/editreason", array("id" => $row->getId()));
        }

        public function getGridUrl()    {
            return $this->getUrl("*/*/gridreason", array("_current" => true));
        }

    }
<?php 
    class Webkul_Mprmasystem_Block_Adminhtml_Allrma_Grid extends Mage_Adminhtml_Block_Widget_Grid {

        public function __construct() {
            parent::__construct();
            $this->setId("rmagrid");
            $this->setUseAjax(true);
            $this->setSaveParametersInSession(true);
        }

        protected function _prepareCollection() {
            $prefix = Mage::getConfig()->getTablePrefix();
            $collection = Mage::getModel("mprmasystem/rmarequest")->getCollection();
            $fnameid = Mage::getModel("eav/entity_attribute")->loadByCode("1", "firstname")->getAttributeId();
            $lnameid = Mage::getModel("eav/entity_attribute")->loadByCode("1", "lastname")->getAttributeId();
            $collection->getSelect()->joinLeft($prefix."sales_flat_order","main_table.orderid = ".$prefix."sales_flat_order.entity_id",array("increment_id"=>"increment_id"));          
            $collection->getSelect()
            ->join(array("ce1" => $prefix."customer_entity_varchar"),"ce1.entity_id = main_table.customerid",array("fname" => "value"))->where("ce1.attribute_id = ".$fnameid)
            ->join(array("ce2" => $prefix."customer_entity_varchar"),"ce2.entity_id = main_table.customerid",array("lname" => "value"))->where("ce2.attribute_id = ".$lnameid)
            ->columns(new Zend_Db_Expr("CONCAT(`ce1`.`value`, ' ',`ce2`.`value`) AS fullname"));
            $collection->addFilterToMap("fullname","CONCAT(`ce1`.`value`, ' ',`ce2`.`value`)");
            $this->setCollection($collection);
            parent::_prepareCollection();
            foreach ($this->getCollection() as $item) {
                $rma_reasoncoll = Mage::getModel("mprmasystem/rmareason")->load($item->getReason());
                $item->reason = $rma_reasoncoll['reason'];
            }
        }

        protected function _prepareColumns() {

            $this->addColumn("id", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Id"),
                "align"     =>  "center",
                "width"     =>  "50px",
                "index"     =>  "id"
            ));

            $this->addColumn("customerid", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Customer Id"),
                "align"     =>  "center",
                "width"     =>  "50px",
                "index"     =>  "customerid"
            ));

            $this->addColumn("name", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Customer Name"),
                "align"     =>  "center",
                "width"     =>  "100px",
                "index"     =>  "fullname",
                "filter_index"=> "fullname"
            ));

            $this->addColumn("orderid", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Order Id"),
                "align"     =>  "center",
                "width"     =>  "100px",
                "index"     =>  "increment_id"
            ));

            $this->addColumn("reason", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Reason"),
                "filter"    => false,
                "sortable"  => false,
                "align"     =>  "left",
                "index"     =>  "reason"
            ));

            $this->addColumn("created_at", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Date"),
                "align"     =>  "left",
                "width"     =>  "150px",
                "index"     =>  "created_at",
                "type"      =>  "datetime",
                "filter_index"=> "main_table.created_at"
            ));

            $this->addColumn("status", array(
                "header"    =>  Mage::helper("mprmasystem")->__("RMA Status"),
                "align"     =>  "center",
                "index"     =>  "status",
                "filter_index"=> "main_table.status"
            ));

            $this->addColumn("sellerstatus", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Status (ADMIN)"),
                "align"     =>  "center",
                "type"      =>  "options",
                "options"   =>  $this->adminstatus(),
                "index"     =>  "sellerstatus"
            ));

            $this->addColumn("customerstatus", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Status (CUSTOMER)"),
                "align"     =>  "center",
                "type"      =>  "options",
                "options"   =>  $this->customerstatus(),
                "index"     =>  "customerstatus"
            ));

            $this->addExportType("*/*/exportCsv", Mage::helper("mprmasystem")->__("CSV"));
            $this->addExportType("*/*/exportXml", Mage::helper("mprmasystem")->__("XML"));
            return parent::_prepareColumns();
        }

        public function getRowUrl($row) {
            return $this->getUrl("*/*/edit", array("id" => $row->getId()));
        }

        public function getGridUrl()    {
            return $this->getUrl("*/*/grid", array("_current" => true));
        }

        public function adminstatus(){
            //$status = array(Mage::helper("mprmasystem")->__("Not Receive Package yet"),Mage::helper("mprmasystem")->__("Received Package"),Mage::helper("mprmasystem")->__("Dispatched Package"),Mage::helper("mprmasystem")->__("Declined/Returned"));
            $status = array("",Mage::helper("mprmasystem")->__("Item Dispatched (Exchanged)"),Mage::helper("mprmasystem")->__("Refunded"),Mage::helper("mprmasystem")->__("Declined"));
            return $status;
        }

        public function customerstatus(){
            $status = array(Mage::helper("mprmasystem")->__("Not Delivered Yet"),Mage::helper("mprmasystem")->__("Delivered"));
            return $status;
        }

    }
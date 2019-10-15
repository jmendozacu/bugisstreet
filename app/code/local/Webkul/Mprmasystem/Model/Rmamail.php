<?php
class Webkul_Mprmasystem_Model_Rmamail extends Mage_Core_Model_Abstract {

      public function NewRmaMail($data,$lastid){
            $rmaid = $lastid;
            $customer = Mage::getSingleton("customer/session")->getCustomer();
            $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
            $adminname = Mage::getStoreConfig("trans_email/ident_general/name");
            $Template = Mage::getModel("core/email_template")->loadDefault("newrma_email");
            $order = Mage::getModel("sales/order")->load($data["order"]);
            $temp_vars = array();
            $temp_vars["recievername"] = "Admin";
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["orderid"] = $order->getIncrementId();
            $temp_vars["reason"] = $data["wk_rs_reason_details"];
            if($data["delivery_status"] == 1)
                $temp_vars["deliverystatus"] = "Delivered";
            else
                $temp_vars["deliverystatus"] = "Not Delivered Yet";
            $temp_vars["consignmentno"] = $data["consignment_no"];
            $temp_vars["rmaurl"] = Mage::helper("adminhtml")->getUrl("mprmasystem/adminhtml_index/edit/", array("id" => $lastid));
            $temp_vars["orderurl"] =  Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view/",array("order_id" => $data["order"]));
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($customer->getName());
            $Template->setSenderEmail($customer->getEmail());
            $Template->send($adminemail,$adminname,$temp_vars);
            //to seller
            $model = Mage::getModel("mprmasystem/rmarequest")->load($rmaid);
            $sellerid = $model->getSellerid();
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();
            $Template = Mage::getModel("core/email_template")->loadDefault("newrma_email");
            $temp_vars = array();
            $temp_vars["recievername"] = $sellername;
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["orderid"] = $order->getIncrementId();
            $temp_vars["reason"] = $data["wk_rs_reason_details"];
            if($data["delivery_status"] == 1)
                $temp_vars["deliverystatus"] = "Delivered";
            else
                $temp_vars["deliverystatus"] = "Not Delivered Yet";
            $temp_vars["consignmentno"] = $data["consignment_no"];
            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/seller/viewrma/", array("id" => $lastid));
            $temp_vars["orderurl"] =  Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view/",array("order_id" => $data["order"]));
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($customer->getName());
            $Template->setSenderEmail($customer->getEmail());
            $Template->send($selleremail,$sellername,$temp_vars);
      }

      public function ConsignmentMail($rmaid,$consignmentnum,$stutus){
            $customer = Mage::getSingleton("customer/session")->getCustomer();
            //to admin
            if(Mage::getStoreConfig('mprmasystem/mprmasystem/notification')==1){
                  $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
                  $adminname = Mage::getStoreConfig("trans_email/ident_general/name");
                  $Template = Mage::getModel("core/email_template")->loadDefault("consignment_email");
                  $temp_vars = array();
                  $temp_vars["recievername"] = "Admin";
                  $temp_vars["rmaid"] = $rmaid;
                  $temp_vars["rmaurl"] = Mage::helper("adminhtml")->getUrl("mprmasystem/adminhtml_index/edit/", array("id" => $rmaid));
                  $temp_vars["consignmentno"] = $consignmentnum;
                  if($stutus == 0)
                      $temp_vars["deliverystatus"] = "Not Delivered Yet";
                  else
                      $temp_vars["deliverystatus"] = "Delivered";            
                  $processedTemplate = $Template->getProcessedTemplate($temp_vars);
                  $Template->setSenderName($customer->getName());
                  $Template->setSenderEmail($customer->getEmail());
                  $Template->send($adminemail,$adminname,$temp_vars);
            }
            //to seller
            $model = Mage::getModel("mprmasystem/rmarequest")->load($rmaid);
            $sellerid = $model->getSellerid();
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();
            $Template = Mage::getModel("core/email_template")->loadDefault("consignment_email");
            $temp_vars = array();
            $temp_vars["recievername"] = $sellername;
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/seller/view/", array("id" => $rmaid));
            $temp_vars["consignmentno"] = $consignmentnum;
            if($stutus == 0)
                $temp_vars["deliverystatus"] = "Not Delivered Yet";
            else
                $temp_vars["deliverystatus"] = "Delivered";
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($customer->getName());
            $Template->setSenderEmail($customer->getEmail());
            $Template->send($selleremail,$sellername,$temp_vars);
      }

      public function ReopnenNMsgMail($rmaid){
            $customer = Mage::getSingleton("customer/session")->getCustomer();
            //to admin
            if(Mage::getStoreConfig('mprmasystem/mprmasystem/notification')==1){
                  $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
                  $adminname = Mage::getStoreConfig("trans_email/ident_general/name");
                  $Template = Mage::getModel("core/email_template")->loadDefault("reopennmsgtoadmin_email");
                  $temp_vars = array();
                  $temp_vars["recievername"] = "Admin";
                  $temp_vars["rmaid"] = $rmaid;            
                  $temp_vars["rmaurl"] = Mage::helper("adminhtml")->getUrl("mprmasystem/adminhtml_index/edit/", array("id" => $rmaid));
                  $processedTemplate = $Template->getProcessedTemplate($temp_vars);
                  $Template->setSenderName($customer->getName());
                  $Template->setSenderEmail($customer->getEmail());
                  $Template->send($adminemail,$adminname,$temp_vars);
            }
            //to seller
            $model = Mage::getModel("mprmasystem/rmarequest")->load($rmaid);
            $sellerid = $model->getSellerid();
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();
            $Template = Mage::getModel("core/email_template")->loadDefault("reopennmsgtoadmin_email");
            $temp_vars = array();
            $temp_vars["recievername"] = $sellername;
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/seller/viewrma/", array("id" => $rmaid));
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($customer->getName());
            $Template->setSenderEmail($customer->getEmail());
            $Template->send($selleremail,$sellername,$temp_vars);
      }

      public function MsgToAdminMail($rmaid){
            $customer = Mage::getSingleton("customer/session")->getCustomer();
            $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
            $adminname = Mage::getStoreConfig("trans_email/ident_general/name");
            //to admin
            if(Mage::getStoreConfig('mprmasystem/mprmasystem/notification')==1){
                  $Template = Mage::getModel("core/email_template")->loadDefault("newmsgtoadmin_email");
                  $temp_vars = array();
                  $temp_vars["sendername"] = "Buyer";
                  $temp_vars["rmaid"] = $rmaid;
                  $temp_vars["customername"] = "Admin";
                  $temp_vars["rmaurl"] = Mage::helper('adminhtml')->getUrl("mprmasystem/adminhtml_index/edit/", array("id" => $rmaid));
                  $processedTemplate = $Template->getProcessedTemplate($temp_vars);
                  $Template->setSenderName($customer->getName());
                  $Template->setSenderEmail($customer->getEmail());
                  $Template->send($adminemail,$adminname,$temp_vars);
            }
            //to seller
            $model = Mage::getModel("mprmasystem/rmarequest")->load($rmaid);
            $sellerid = $model->getSellerid();
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();
            $Template = Mage::getModel("core/email_template")->loadDefault("newmsgtocustomer_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Buyer";
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["customername"] = $sellername;
            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/seller/viewrma/", array("id" => $rmaid));
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($customer->getName());
            $Template->setSenderEmail($customer->getEmail());
            $Template->send($selleremail,$sellername,$temp_vars);
      }

      public function CancelRMAMailbycustomer($rmaid){
            $customer = Mage::getSingleton("customer/session")->getCustomer();
            //to admin
            if(Mage::getStoreConfig('mprmasystem/mprmasystem/notification')==1){
                  $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
                  $adminname = Mage::getStoreConfig("trans_email/ident_general/name");
                  $Template = Mage::getModel("core/email_template")->loadDefault("rmacanceled_email");
                  $temp_vars = array();
                  $temp_vars["sendername"] = "Buyer";
                  $temp_vars["recievername"] = "Admin";
                  $temp_vars["rmaid"] = $rmaid;
                  $processedTemplate = $Template->getProcessedTemplate($temp_vars);
                  $Template->setSenderName($customer->getName());
                  $Template->setSenderEmail($customer->getEmail());
                  $Template->send($adminemail,$adminname,$temp_vars);
            }
            //to seller
            $model = Mage::getModel("mprmasystem/rmarequest")->load($rmaid);
            $sellerid = $model->getSellerid();
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();
            $Template = Mage::getModel("core/email_template")->loadDefault("rmacanceled_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Buyer";
            $temp_vars["recievername"] = $sellername;
            $temp_vars["rmaid"] = $rmaid;
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($customer->getName());
            $Template->setSenderEmail($customer->getEmail());
            $Template->send($selleremail,$sellername,$temp_vars);
      }

      public function CancelRMAMailbyadmin($rmaid){
            $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
            $adminname = Mage::getStoreConfig("trans_email/ident_general/name");
            $model = Mage::getModel("mprmasystem/rmarequest")->load($rmaid);

            //to buyer
            $customerid = $model->getCustomerid();
            $customer = Mage::getModel("customer/customer")->load($customerid);
            $customeremail = $customer->getEmail();
            $customername = $customer->getName();            
            $Template = Mage::getModel("core/email_template")->loadDefault("rmacanceled_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Admin";
            $temp_vars["recievername"] = $customername;
            $temp_vars["rmaid"] = $rmaid;
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($adminname);
            $Template->setSenderEmail($adminemail);
            $Template->send($customeremail,$customername,$temp_vars);

            //to seller
            $sellerid = $model->getSellerid();
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();
            $Template = Mage::getModel("core/email_template")->loadDefault("rmacanceled_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Admin";
            $temp_vars["recievername"] = $sellername;
            $temp_vars["rmaid"] = $rmaid;
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($adminname);
            $Template->setSenderEmail($adminemail);
            $Template->send($selleremail,$sellername,$temp_vars);
      }

      public function CancelRMAMailbyseller($rmaid){
            $model  = Mage::getModel("mprmasystem/rmarequest")->load($rmaid);
            $sellerid = $model->getSellerid();
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();

            //to buyer
            $customerid = $model->getCustomerid();
            $customer = Mage::getModel("customer/customer")->load($customerid);
            $customeremail = $customer->getEmail();
            $customername = $customer->getName();            
            $Template = Mage::getModel("core/email_template")->loadDefault("rmacanceled_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Seller";
            $temp_vars["recievername"] = $customername;
            $temp_vars["rmaid"] = $rmaid;
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($sellername);
            $Template->setSenderEmail($selleremail);
            $Template->send($customeremail,$customername,$temp_vars);

            //to Admin
            if(Mage::getStoreConfig('mprmasystem/mprmasystem/notification')==1){
                  $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
                  $adminname = Mage::getStoreConfig("trans_email/ident_general/name");
                  $Template = Mage::getModel("core/email_template")->loadDefault("rmacanceled_email");
                  $temp_vars = array();
                  $temp_vars["sendername"] = "Seller";
                  $temp_vars["recievername"] = "Admin";
                  $temp_vars["rmaid"] = $rmaid;
                  $processedTemplate = $Template->getProcessedTemplate($temp_vars);
                  $Template->setSenderName($sellername);
                  $Template->setSenderEmail($selleremail);
                  $Template->send($adminemail,$adminname,$temp_vars);
            }
      }

      public function SolvedRMAMail($rmaid){
            $customer = Mage::getSingleton("customer/session")->getCustomer();
            //to admin
            if(Mage::getStoreConfig('mprmasystem/mprmasystem/notification')==1){
                  $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
                  $adminname = Mage::getStoreConfig("trans_email/ident_general/name");
                  $Template = Mage::getModel("core/email_template")->loadDefault("rmasolvednclosed_email");
                  $temp_vars = array();
                  $temp_vars["sendername"] = "Buyer";
                  $temp_vars["recievername"] = "Admin";
                  $temp_vars["rmaid"] = $rmaid;
                  $processedTemplate = $Template->getProcessedTemplate($temp_vars);
                  $Template->setSenderName($customer->getName());
                  $Template->setSenderEmail($customer->getEmail());
                  $Template->send($adminemail,$adminname,$temp_vars);
            }
            //to seller
            $model = Mage::getModel("mprmasystem/rmarequest")->load($rmaid);
            $sellerid = $model->getSellerid();
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();
            $Template = Mage::getModel("core/email_template")->loadDefault("rmasolvednclosed_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Buyer";
            $temp_vars["recievername"] = $sellername;
            $temp_vars["rmaid"] = $rmaid;
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($customer->getName());
            $Template->setSenderEmail($customer->getEmail());
            $Template->send($selleremail,$sellername,$temp_vars);
      }

      public function MsgToCustomerMail($rmaid,$customerid){
            $customer = Mage::getModel("customer/customer")->load($customerid);
            $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
            $adminname = Mage::getStoreConfig("trans_email/ident_general/name");

            // to buyer
            $Template = Mage::getModel("core/email_template")->loadDefault("newmsgtocustomer_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Admin";
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["customername"] = $customer->getName();
            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/index/view/", array("id" => $rmaid));
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($adminname);
            $Template->setSenderEmail($adminemail);
            $Template->send($customer->getEmail(),$customer->getName(),$temp_vars);

            //to seller
            $model = Mage::getModel("mprmasystem/rmarequest")->load($rmaid);
            $sellerid = $model->getSellerid();
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();
            $Template = Mage::getModel("core/email_template")->loadDefault("newmsgtocustomer_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Admin";
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["customername"] = $sellername;
            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/seller/viewrma/", array("id" => $rmaid));
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($adminname);
            $Template->setSenderEmail($adminemail);
            $Template->send($selleremail,$sellername,$temp_vars);
      }

      public function AdminStatusMail($adminstatus,$rmaid,$customerid){
            $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
            $adminname = Mage::getStoreConfig("trans_email/ident_general/name");

            //to buyer
            $customer = Mage::getModel("customer/customer")->load($customerid);
            $customeremail = $customer->getEmail();
            $customername = $customer->getName();            
            $Template = Mage::getModel("core/email_template")->loadDefault("packagestatusatadmin_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Admin";
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["customername"] = $customer->getName();
            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/adminhtml_index/edit/id/", array("id" => $rmaid));
            if($adminstatus == 0){
                $temp_vars["packegestatus"] = "Not Receive Package yet";
                $temp_vars["rmastatus"] = "Pending";
            }
            if($adminstatus == 1){
                //$temp_vars["packegestatus"] = "Received Package";
                $temp_vars["packegestatus"] = "Item Dispatched (Exchanged)";
                $temp_vars["rmastatus"] = "Processing";
            }
            if($adminstatus == 2){
                //$temp_vars["packegestatus"] = "Dispatched Package";
                $temp_vars["packegestatus"] = "Refunded";
                $temp_vars["rmastatus"] = "Processing";
            }
            if($adminstatus == 3){
                //$temp_vars["packegestatus"] = "Declined/Returned";
                $temp_vars["packegestatus"] = "Declined";
                $temp_vars["rmastatus"] = "Solved";
            }
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($adminname);
            $Template->setSenderEmail($adminemail);
            $Template->send($customer->getEmail(),$customer->getName(),$temp_vars);

            //to seller
            $model = Mage::getModel("mprmasystem/rmarequest")->load($rmaid);
            $sellerid = $model->getSellerid();
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();
            $Template = Mage::getModel("core/email_template")->loadDefault("packagestatusatadmin_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Admin";
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["customername"] = $sellername;
            if($adminstatus == 0){
                $temp_vars["packegestatus"] = "Not Receive Package yet";
                $temp_vars["rmastatus"] = "Pending";
            }
            if($adminstatus == 1){
                //$temp_vars["packegestatus"] = "Received Package";
                $temp_vars["packegestatus"] = "Item Dispatched (Exchanged)";
                $temp_vars["rmastatus"] = "Processing";
            }
            if($adminstatus == 2){
                //$temp_vars["packegestatus"] = "Dispatched Package";
                $temp_vars["packegestatus"] = "Refunded";
                $temp_vars["rmastatus"] = "Processing";
            }
            if($adminstatus == 3){
                //$temp_vars["packegestatus"] = "Declined/Returned";
                $temp_vars["packegestatus"] = "Declined";
                $temp_vars["rmastatus"] = "Solved";
            }
            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/seller/viewrma/", array("id" => $rmaid));
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($adminname);
            $Template->setSenderEmail($adminemail);
            $Template->send($selleremail,$sellername,$temp_vars);
      }

      public function SellerToCustomerMail($sellerid,$rmaid,$customerid,$rmamsg){
            $customer = Mage::getModel("customer/customer")->load($customerid);
            $customeremail = $customer->getEmail();
            $customername = $customer->getName();

            // to buyer
            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();
            $Template = Mage::getModel("core/email_template")->loadDefault("newmsgtocustomer_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Seller";
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["customername"] = $customer->getName();
            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/index/view/", array("id" => $rmaid));
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($sellername);
            $Template->setSenderEmail($selleremail);
            $Template->send($customeremail,$customername,$temp_vars);

            //to admin
            if(Mage::getStoreConfig('mprmasystem/mprmasystem/notification')==1){
                  $adminemail = Mage::getStoreConfig("trans_email/ident_general/email");
                  $adminname = Mage::getStoreConfig("trans_email/ident_general/name");
                  $Template = Mage::getModel("core/email_template")->loadDefault("newmsgtoadmin_email");
                  $temp_vars = array();
                  $temp_vars["sendername"] = "Seller";
                  $temp_vars["rmaid"] = $rmaid;
                  $temp_vars["rmamsg"] = $rmamsg;
                  $temp_vars["customername"] = $adminname;
                  $temp_vars["rmaurl"] = Mage::helper("adminhtml")->getUrl("mprmasystem/adminhtml_index/edit/", array("id" => $rmaid));
                  $processedTemplate = $Template->getProcessedTemplate($temp_vars);
                  $Template->setSenderName($sellername);
                  $Template->setSenderEmail($selleremail);
                  $Template->send($adminemail,$adminname,$temp_vars);
            }
      }

      public function SellerStatusMail($sellerid,$sellerstatus,$rmaid,$customerid){
            $customer = Mage::getModel("customer/customer")->load($customerid);
            $customeremail = $customer->getEmail();
            $customername = $customer->getName();

            $seller = Mage::getModel("customer/customer")->load($sellerid);
            $selleremail = $seller->getEmail();
            $sellername = $seller->getName();

            // to customer
            $Template = Mage::getModel("core/email_template")->loadDefault("packagestatusatadmin_email");
            $temp_vars = array();
            $temp_vars["sendername"] = "Seller";
            $temp_vars["rmaid"] = $rmaid;
            $temp_vars["customername"] = $customer->getName();
            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/index/view/", array("id" => $rmaid));
//            $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/adminhtml_index/edit/id/", array("id" => $rmaid));
//            $temp_vars["rmaurl"] = Mage::helper('adminhtml')->getUrl('adminhtml/name_of_custom_extension/name_of_controller/');
            if($sellerstatus == 0){
                $temp_vars["packegestatus"] = "Not Receive Package yet";
                $temp_vars["rmastatus"] = "Pending";
            }
            if($sellerstatus == 1){
                //$temp_vars["packegestatus"] = "Received Package";
                $temp_vars["packegestatus"] = "Item Dispatched (Exchanged)";
                $temp_vars["rmastatus"] = "Processing";
            }
            if($sellerstatus == 2){
                //$temp_vars["packegestatus"] = "Dispatched Package";
                $temp_vars["packegestatus"] = "Refunded";

                $temp_vars["rmastatus"] = "Processing";
            }
            if($sellerstatus == 3){
                //$temp_vars["packegestatus"] = "Declined/Returned";
                $temp_vars["packegestatus"] = "Declined";
                $temp_vars["rmastatus"] = "Solved";
            }

            //$EmailToAdmin = Mage::getStoreConfig("trans_email/ident_general/email");
            $processedTemplate = $Template->getProcessedTemplate($temp_vars);
            $Template->setSenderName($sellername);
            $Template->setSenderEmail($selleremail);
            $Template->send($customeremail,$customername,$temp_vars);
            //$Template->send($EmailToAdmin,'Admin',$temp_vars);




            //to admin
          $Template = Mage::getModel("core/email_template")->loadDefault("packagestatusatadmin_email");
          $temp_vars = array();
          $temp_vars["sendername"] = "Seller";
          $temp_vars["rmaid"] = $rmaid;
          //$temp_vars["customername"] = $customer->getName();
          $temp_vars["customername"] = Mage::getStoreConfig("trans_email/ident_general/name");
          $temp_vars["rmaurl"] = Mage::getUrl("mprmasystem/index/view/", array("id" => $rmaid));

          if($sellerstatus == 0){
              $temp_vars["packegestatus"] = "Not Receive Package yet";
              $temp_vars["rmastatus"] = "Pending";
          }
          if($sellerstatus == 1){
              //$temp_vars["packegestatus"] = "Received Package";
              $temp_vars["packegestatus"] = "Item Dispatched (Exchanged)";
              $temp_vars["rmastatus"] = "Processing";
          }
          if($sellerstatus == 2){
              //$temp_vars["packegestatus"] = "Dispatched Package";
              $temp_vars["packegestatus"] = "Refunded";

              $temp_vars["rmastatus"] = "Processing";
          }
          if($sellerstatus == 3){
              //$temp_vars["packegestatus"] = "Declined/Returned";
              $temp_vars["packegestatus"] = "Declined";
              $temp_vars["rmastatus"] = "Solved";
          }

          $EmailToAdmin = Mage::getStoreConfig("trans_email/ident_general/email");
          $processedTemplate = $Template->getProcessedTemplate($temp_vars);
          $Template->setSenderName($sellername);
          $Template->setSenderEmail($selleremail);
          $Template->send($EmailToAdmin,'Admin',$temp_vars);

      }
}
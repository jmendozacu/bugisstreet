<?php
    class Webkul_Mprmasystem_Adminhtml_Mprmasystem_IndexController extends Mage_Adminhtml_Controller_Action {

        protected function _initAction() {
            $this->loadLayout()->_setActiveMenu("mprmasystem");
            $this->getLayout()->getBlock("head")->setTitle(Mage::helper("mprmasystem")->__("RMA System"));
            return $this;
        }
        
        public function indexAction() {
            $this->_initAction()->renderLayout();
        }

        public function gridAction()    {
            $this->loadLayout();
            $this->getResponse()->setBody(
                   $this->getLayout()->createBlock("mprmasystem/adminhtml_allrma_grid")->toHtml()
            );
        }

        public function conversationgridAction()    {
            $this->loadLayout();
            $this->getResponse()->setBody(
                   $this->getLayout()->createBlock("mprmasystem/adminhtml_allrma_edit_tab_conversation")->toHtml()
            );
        }

        public function editAction() {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("mprmasystem/rmarequest")->load($id);
            $order = Mage::getModel("sales/order")->load($model->getOrderid())->getIncrementId();
            $name = Mage::getModel("customer/customer")->load($model->getCustomerid())->getName();
            if($model->getCustomerstatus() == 0)
                $model->setCustomerstatus("Not Delivered Yet");
            else
                $model->setCustomerstatus("Delivered");
            $model->setIncrementid($order);
            $model->setName($name);
            $rma_reasoncoll = Mage::getModel("mprmasystem/rmareason")->load($model->getReason());
            $model->setReason($rma_reasoncoll['reason']);
            if($model->getId() || $id == 0) {
                $data = Mage::getSingleton("adminhtml/session")->getFormData(false);
                if (!empty($data))
                    $model->setData($data);
                Mage::register("mprma_data", $model);
                $this->loadLayout();
                $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
                $this->_addContent($this->getLayout()->createBlock("mprmasystem/adminhtml_allrma_edit"))
                        ->_addLeft($this->getLayout()->createBlock("mprmasystem/adminhtml_allrma_edit_tabs"));
                $this->renderLayout();
            }
            else {
                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("mprmasystem")->__("Item does not exist"));
                $this->_redirect("*/*/");
            }
        }

        public function updateAction() {
            $id = $this->getRequest()->getParam("id");
            $adminstatus = $this->getRequest()->getParam("sellerstatus");
            $model = Mage::getModel("mprmasystem/rmarequest")->load($id);
            $customerid = $model->getCustomerid();
            $model->setSellerstatus($adminstatus);
            if($adminstatus == 0)
                Mage::getModel("mprmasystem/rmastatus")->setPending($id);
            if($adminstatus == 1 || $adminstatus == 2)
                Mage::getModel("mprmasystem/rmastatus")->setProcessing($id);
            if($adminstatus == 3)
                Mage::getModel("mprmasystem/rmastatus")->setCancelbyadmin($id);
            $model->setStatus($status);
            $rmaid = $model->save()->getId();
            if($rmaid > 0)
                Mage::getModel("mprmasystem/rmamail")->AdminStatusMail($adminstatus,$id,$customerid);
            $message = $this->getRequest()->getParam("convmessage");
            if($message){
                $conmodel = Mage::getModel("mprmasystem/conversation");
                $conmodel->setRmaid($id);
                $conmodel->setMessage($message);
                $conmodel->setCreatedAt(time());
                $conmodel->setSender("Admin");   
                $conmodel->setSendertype(2);   
                $msgid = $conmodel->save()->getId();
                if($msgid > 0)
                    Mage::getModel("mprmasystem/rmamail")->MsgToCustomerMail($id,$customerid);
            }
            Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("mprmasystem")->__("Status Updated Successfully"));
            if($this->getRequest()->getParam("back"))
                $this->_redirect("*/*/edit", array("id" => $model->getId()));
            else    
                $this->_redirect("*/*/");
        }

        protected function reasonAction() {
            $this->loadLayout()->_setActiveMenu("mprmasystem");
            $this->getLayout()->getBlock("head")->setTitle(Mage::helper("mprmasystem")->__("RMA Reasons"));
            $this->renderLayout();
            return $this;
        }

        public function newreasonAction() {
            $this->_forward("editreason");
        }

        public function editreasonAction() {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("mprmasystem/rmareason")->load($id);
            if($model->getId() || $id == 0) {
                $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
                if(!empty($data))
                    $model->setData($data);
                Mage::register("mprmareason_data", $model);
                $this->loadLayout();
                $this->_setActiveMenu("mprmasystem");
                $this->getLayout()->getBlock("head")->setTitle(Mage::helper("mprmasystem")->__("RMA Reasons"));
                $this->_addContent($this->getLayout()->createBlock("mprmasystem/adminhtml_rmareason_edit"))
                        ->_addLeft($this->getLayout()->createBlock("mprmasystem/adminhtml_rmareason_edit_tabs"));
                $this->renderLayout();
            }
            else {
                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("mprmasystem")->__("Item does not exist"));
                $this->_redirect("*/*/reason");
            }
        }

        public function savereasonAction() {        
            if($data = $this->getRequest()->getPost()) {                
                $model = Mage::getModel("mprmasystem/rmareason");
                $model->setData($data)->setId($this->getRequest()->getParam("id"));
                try{
                    $model->save();
                    Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("mprmasystem")->__("Item was successfully saved"));
                    Mage::getSingleton("adminhtml/session")->setFormData(false);
                    if($this->getRequest()->getParam("back")) {
                        $this->_redirect("*/*/editreason", array("id" => $model->getId()));
                        return;
                    }
                    $this->_redirect("*/*/reason");
                    return;
                }
                catch(Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    Mage::getSingleton("adminhtml/session")->setFormData($data);
                    $this->_redirect("*/*/editreason", array("id" => $this->getRequest()->getParam("id")));
                    return;
                }
            }
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("mprmasystem")->__("Unable to find item to save"));
            $this->_redirect("*/*/reason");
        }

        public function deletereasonAction() {
            if ($this->getRequest()->getParam("id") > 0) {
                try {
                    $model = Mage::getModel("mprmasystem/rmareason")->load($this->getRequest()->getParam("id"));
                    $model->delete();
                    Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
                    $this->_redirect("*/*/");
                }
                catch(Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    $this->_redirect("*/*/editreason", array("id" => $this->getRequest()->getParam("id")));
                }
            }
            $this->_redirect("*/*/reason");
        }

        public function massDeletereasonAction() {
            $Ids = $this->getRequest()->getParam("ids");
            if(!is_array($Ids))
                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("adminhtml")->__("Please select item (s)"));
            else {
                try {
                    foreach($Ids as $Id)
                        Mage::getModel("mprmasystem/rmareason")->load($Id)->delete();
                    Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Total of ").count($Ids).Mage::helper("adminhtml")->__(" record(s) were successfully deleted"));
                }
                catch(Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/reason");
        }

        public function massStatusreasonAction() {
            $Ids = $this->getRequest()->getParam("ids");
            if(!is_array($Ids))
                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("adminhtml")->__("Please select item (s)"));
            else {
                try {
                    foreach($Ids as $Id)
                        Mage::getSingleton("mprmasystem/rmareason")->load($Id)->setStatus($this->getRequest()->getParam("status"))->setIsMassupdate(true)->save();
                    $this->_getSession()->addSuccess(Mage::helper("mprmasystem")->__("Total of ").count($Ids).Mage::helper("mprmasystem")->__(" record(s) were successfully updated"));
                }
                catch(Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/reason");
        }

        public function exportCsvreasonAction() {
            $fileName = "rmareason.csv";
            $content = $this->getLayout()->createBlock("mprmasystem/adminhtml_rmareason_grid")->getCsv();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlreasonAction() {
            $fileName = "rmareason.xml";
            $content = $this->getLayout()->createBlock("mprmasystem/adminhtml_rmareason_grid")->getXml();
            $this->_sendUploadResponse($fileName, $content);
        }

        protected function _sendUploadResponse($fileName, $content, $contentType="application/octet-stream") {
            $response = $this->getResponse();
            $response->setHeader("HTTP/1.1 200 OK", "");
            $response->setHeader("Pragma", "public", true);
            $response->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true);
            $response->setHeader("Content-Disposition", "attachment; filename=" . $fileName);
            $response->setHeader("Last-Modified", date("r"));
            $response->setHeader("Accept-Ranges", "bytes");
            $response->setHeader("Content-Length", strlen($content));
            $response->setHeader("Content-type", $contentType);
            $response->setBody($content);
            $response->sendResponse();
            die;
        }

        public function gridreasonAction()  {
            $this->loadLayout();
            $this->getResponse()->setBody($this->getLayout()->createBlock("mprmasystem/adminhtml_rmareason_grid")->toHtml());
        }

        public function exportCsvAction() {
            $fileName = "rma.csv";
            $content = $this->getLayout()->createBlock("mprmasystem/adminhtml_allrma_grid")->getCsv();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlAction() {
            $fileName = "rma.xml";
            $content = $this->getLayout()->createBlock("mprmasystem/adminhtml_allrma_grid")->getXml();
            $this->_sendUploadResponse($fileName, $content);
        }

    } 
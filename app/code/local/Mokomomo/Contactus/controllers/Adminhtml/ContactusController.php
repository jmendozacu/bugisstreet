<?php
class Mokomomo_Contactus_Adminhtml_ContactusController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('contactus');
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
             ->_addContent($this->getLayout()->createBlock('contactus/adminhtml_contactus'))
			->renderLayout();

	}
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('contactus/adminhtml_contactus_grid')->toHtml());
    }
    
    public function newAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('contactus');
        $this->_addContent($this->getLayout()->createBlock('contactus/adminhtml_contactus_new')->setTemplate('mokomomo/contactus/new.phtml'));
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');        
        try {
            Mage::getModel('contactus/contactus')->load($id)->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess('Total of 1 record were successfully saved');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/index');
    }
}
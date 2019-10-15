<?php
class Webkul_Mpshippingmanager_Block_Salesdetail extends Mage_Core_Block_Template
{
	public function __construct() {
        parent::__construct();	
		$collection=$this->getOrderDetail();
        $this->setCollection($collection);
    }
	public function _prepareLayout(){
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(30=>30,60=>60,90=>90,'all'=>'all'));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }
	public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }
    
	private function getOrderDetail(){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$trackingCollection=Mage::getModel('mpshippingmanager/tracking')->getCollection();
		$mytrackingarray=array();
		foreach($trackingCollection as $key => $tcc){
			if($tcc->getItemIds()){
			 	array_push($mytrackingarray , $tcc->getOrderId());
			}
		}
		try{
			
		$orderslist=Mage::getModel('marketplace/saleslist')->getCollection()
										 ->addFieldToFilter('mageproownerid',array('eq'=>$customerid))
										 ->addFieldToFilter('mageproid',array('eq'=>$this->getRequest()->getParam('id')))
										 ->addFieldToFilter('mageorderid', array('in' => $mytrackingarray))
										 ->setOrder('autoid','DESC');
		}
		catch(Exception $e){
			echo $e->getMessage();
		}
	    return $orderslist;
	}
}

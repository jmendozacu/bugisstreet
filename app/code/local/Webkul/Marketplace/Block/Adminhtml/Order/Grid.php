<?php
class Webkul_Marketplace_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
    parent::__construct();
    $this->setId('marketplaceGrid');
    $this->setUseAjax(true);
    
    $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {   
    $customerid=$this->getRequest()->getParam('id');
    $collection = Mage::getModel('marketplace/saleslist')->getCollection();
    $collection->addFieldToFilter('mageproownerid',array('eq'=>$customerid));
    $collection->addFieldToFilter('magerealorderid',array('neq'=>0));
    //$collection->setOrder('cpprostatus');
    //$collection->setOrder('paidstatus','ASC');
    $prefix = Mage::getConfig()->getTablePrefix();
    $collection->getSelect()
        ->join(array("ccp" => $prefix."sales_flat_order"),"ccp.entity_id = main_table.mageorderid",array("status" => "status"))
        ->join(array("ccp2" => $prefix."sales_flat_order_item"),"ccp2.product_id = main_table.mageproid AND ccp2.order_id = main_table.mageorderid",array("item_id" => "item_id"));
    $this->setCollection($collection);
    parent::_prepareCollection();
    foreach ($collection as $item) {
      $item->view='<a class="wk_sellerorderstatus" wk_cpprostatus="'.$item->getCpprostatus().'" href="'.$this->getUrl('adminhtml/sales_order/view/',array('order_id'=>$item->getMageorderid())).'" title="'.Mage::helper('marketplace')->__('View Order').'">'.Mage::helper('marketplace')->__('View Order').'</a>';
      $item_invoice = 0;
      $invoice = Mage::getResourceModel('sales/order_invoice_item_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('order_item_id', array('eq' => $item->getItemId()))
                ->addAttributeToFilter('product_id', array('eq' => $item->getMageproid()))
                ->load();
      foreach ($invoice as $value) {
        $item_invoice = 1;
      }
      
      if(($item->getPaidstatus()==0) && ($item->getCpprostatus()==1) && ($item->getStatus()=='complete'||$item_invoice)){
        $item->payseller='<button type="button" class="button wk_payseller" auto-id="'.$item->getAutoid().'" title="'.Mage::helper('marketplace')->__('Pay Seller').'"><span><span><span>'.Mage::helper('marketplace')->__('Pay Seller').'</span></span></span></button>';
      }
      else if(($item->getPaidstatus()==0||$item->getPaidstatus()==4) && ($item->getCpprostatus()==0)){
        $item->payseller=Mage::helper('marketplace')->__('Order Pending');
      }else if(($item->getPaidstatus()==0 || $item->getPaidstatus()==4 || $item->getPaidstatus()==2) && ($item->getCpprostatus()==1) && ($item->getStatus()!='complete')){
        $item->payseller=Mage::helper('marketplace')->__('Order Pending');
      }else{
        if(strpos($item->getStatus(),'cancel') !== false && $item->getPaidstatus()==2){
          $item->payseller=Mage::helper('marketplace')->__('Order Cancelled');
        }else{
          $item->payseller=Mage::helper('marketplace')->__('Already Paid');
        }
      }
    }   
  }

  protected function _prepareColumns(){
    $this->addColumn('mageorderid', array(
      'header'    => Mage::helper('marketplace')->__('Order ID'),
      'width'     => '50px',
      'index'     => 'mageorderid',
    ));
    $this->addColumn('magerealorderid', array(
      'header'    => Mage::helper('marketplace')->__('Order#'),
      'index'     => 'magerealorderid',
      'width' => '80px',
    ));    
    $this->addColumn('cleared_at', array(
      'header'    => Mage::helper('marketplace')->__('Purchased On'),
      'type'      => 'datetime',
      'index'     => 'cleared_at',
      'width' => '100px',
    ));
    $this->addColumn('mageproname', array(
      'header'    => Mage::helper('marketplace')->__('Product Name'),
      'index'     => 'mageproname',
    ));
    $this->addColumn('magequantity', array(
      'header'    => Mage::helper('marketplace')->__('Product Quantity'),
      'index'     => 'magequantity',
      'width' => '100px',
    ));
    $this->addColumn('mageproprice', array(
      'header'    => Mage::helper('marketplace')->__('Product Price'),
      'index'     => 'mageproprice',
      'currency_code' => $this->getcurrency(),
      'type'      => 'currency',
      'column_css_class' => 'wkmageproprice',
    ));
    $this->addColumn('totalamount', array(
      'header'    => Mage::helper('marketplace')->__('Total Amount'),
      'index'     => 'totalamount',
      'currency_code' => $this->getcurrency(),
      'type'      => 'currency',
      'column_css_class' => 'wktotalamount',
    ));
    $this->addColumn('totaltax', array(
      'header'    => Mage::helper('marketplace')->__('Total Tax'),
      'index'     => 'totaltax',
      'currency_code' => $this->getcurrency(),
      'type'      => 'currency',
      'column_css_class' => 'wktotaltax',
    ));
    $this->addColumn('totalcommision', array(
      'header'    => Mage::helper('marketplace')->__('Total Commission'),
      'index'     => 'totalcommision',
      'currency_code' => $this->getcurrency(),
      'type'      => 'currency',
      'column_css_class' => 'wktotalcommision',
    ));
    $this->addColumn('actualparterprocost', array(
      'header'    => Mage::helper('marketplace')->__('Total Seller Amount'),
      'index'     => 'actualparterprocost',
      'currency_code' => $this->getcurrency(),
      'type'      => 'currency',
      'column_css_class' => 'wkactualparterprocost',
    ));
    $this->addColumn('status', array(
      'header'    => Mage::helper('marketplace')->__('Status'),
      'index'     => 'status',
      'type'      => 'options',
      'column_css_class' => 'wk_orderstatus',
      'width'     => '70px',
      'options'   => Mage::getSingleton('sales/order_config')->getStatuses(),
    ));
    $this->addColumn('paidstatus', array(
      'header' => Mage::helper('sales')->__('Paid Status'),
      'index' => 'paidstatus',
      'type'  => 'options',
      'width' => '70px',
      'column_css_class' => 'wk_paidstatus',
      'options' => $this->getpaidStatuses(),
    ));
    $this->addColumn('view', array(
      'header'    => Mage::helper('customer')->__('View'),
      'index'     => 'view',
      'type'      => 'text',
      'filter'    => false,
      'sortable'  => false
    ));
    $this->addColumn('payseller', array(
      'header'    => Mage::helper('marketplace')->__('Pay'),
      'index'     => 'payseller',
      'type'      => 'text',
      'filter'    => false,
      'sortable'  => false
    ));
    return parent::_prepareColumns();
  }

  protected function _prepareMassaction(){
    $this->setMassactionIdField('mageorderid');
    $this->getMassactionBlock()->setFormFieldName('sellerorderids');
    $this->getMassactionBlock()->setUseSelectAll(false);
    $this->getMassactionBlock()->setUseUnSelectAll(false);

    $this->getMassactionBlock()->addItem('pay', array(
     'label'    => Mage::helper('customer')->__('Pay'),
     'url'      => $this->getUrl('*/*/masspay'),
     'confirm' => Mage::helper('tax')->__('Are you want to make this payment?')
    ));
    return $this;
  }
  public function getGridUrl(){
    return $this->getUrl("*/*/grid",array("_current"=>true));
  }
  public function getRowUrl($row) {
    return '#';
  }
  public function getcurrency(){        
    return (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
  }
  public function getpaidStatuses(){
    return array('0'=>'Pending','1'=>'Paid','2'=>'Hold','3'=>'Refunded','4'=>'Voided');
  }
}
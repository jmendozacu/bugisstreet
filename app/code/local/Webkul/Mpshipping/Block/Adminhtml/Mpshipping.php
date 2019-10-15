<?php
class Webkul_Mpshipping_Block_Adminhtml_Mpshipping extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_mpshipping';
    $this->_blockGroup = 'mpshipping';
    $this->_headerText = Mage::helper('mpshipping')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('mpshipping')->__('Add Item');
    parent::__construct();
	$this->_removeButton('add');
  }
}
<?php
class Webkul_Mpshippingmanager_Block_Adminhtml_Ordermanage extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
        $this->_controller = 'adminhtml_ordermanage';
        $this->_headerText = Mage::helper('mpshippingmanager')->__('Customers Order');
        $this->_blockGroup = 'mpshippingmanager';
        parent::__construct();
        $this->_removeButton('add');
		$this->_removeButton('reset_filter_button');
		$this->_removeButton('search_button');   
    }	
}

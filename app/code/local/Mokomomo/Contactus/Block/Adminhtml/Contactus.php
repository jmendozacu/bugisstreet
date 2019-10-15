<?php
class Mokomomo_Contactus_Block_Adminhtml_Contactus extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_contactus';
        $this->_blockGroup = 'contactus';
        $this->_headerText = 'E-Mail Configuration';
        $this->_addButtonLabel = 'Add E-Mail';
        parent::__construct();
    }
}
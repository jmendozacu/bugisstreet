<?php
class Mokomomo_Contactus_IndexController extends Unirgy_Dropship_Controller_VendorAbstract
{
    public function indexAction()
    {
        parent::_setTheme();
        $this->loadLayout();
        $this->renderLayout();      
    }

}
<?php

class Webkul_Mpshipping_Model_Mpshipping extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mpshipping/mpshipping');
    }
}
<?php

class Webkul_Mpshipping_Model_Mysql4_Mpshipping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mpshipping/mpshipping');
    }
}
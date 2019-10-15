<?php

class Webkul_Mpshipping_Model_Mysql4_Mpshipping extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the mpshipping_id refers to the key field in your database table.
        $this->_init('mpshipping/mpshipping', 'mpshipping_id');
    }
}
<?php
class Webkul_Marketplace_Model_Mysql4_Userprofile extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('marketplace/userprofile', 'autoid');
    }
}
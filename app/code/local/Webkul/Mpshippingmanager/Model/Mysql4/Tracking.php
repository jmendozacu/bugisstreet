<?php

class Webkul_Mpshippingmanager_Model_Mysql4_Tracking extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("mpshippingmanager/tracking", "tracking_id");
    }
}

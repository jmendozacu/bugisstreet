<?php

class Mokomomo_Contactus_Model_Resource_Contactus_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    protected function _construct()
    {
        $this->_init("contactus/contactus");
    }
}
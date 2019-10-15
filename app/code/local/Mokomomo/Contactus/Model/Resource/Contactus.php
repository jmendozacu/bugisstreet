<?php

class Mokomomo_Contactus_Model_Resource_Contactus extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct()
    {
        $this->_init("contactus/contactus", 'contactus_id');
    }
}

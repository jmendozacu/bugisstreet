<?php
	class Webkul_ChatSystem_Model_Conversation extends Mage_Core_Model_Abstract	{

    	public function _construct()    {
	        parent::_construct();
	        $this->_init("chatsystem/conversation");
    	}

    }
<?php
	class Webkul_ChatSystem_Model_Mysql4_Conversation extends Mage_Core_Model_Mysql4_Abstract		{

	    public function _construct()	{    
	        $this->_init("chatsystem/conversation","id");
	    }
	    
	}
<?php
	class Webkul_Mprmasystem_Model_Mysql4_Conversation_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract	{
		
    	public function _construct()	{
		    parent::_construct();
		    $this->_init("mprmasystem/conversation");
    	}
    	
	}
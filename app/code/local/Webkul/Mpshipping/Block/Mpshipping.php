<?php
class Webkul_Mpshipping_Block_Mpshipping extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getMpshipping()     
     { 
        if (!$this->hasData('mpshipping')) {
            $this->setData('mpshipping', Mage::registry('mpshipping'));
        }
        return $this->getData('mpshipping');
        
    }
}
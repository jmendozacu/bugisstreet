<?php
	class Webkul_Mprmasystem_Block_mpsellerallrma extends Mage_Customer_Block_Account	{
		
		public function __construct()    {
            parent::__construct();
    		$customerid = Mage::getSingleton("customer/session")->getCustomerId();
    		$collection = Mage::getResourceModel("mprmasystem/rmarequest_collection")->addFieldToFilter("sellerid",$customerid)
                          ->setOrder('id','DESC');
            $this->setCollection($collection);
        }

        protected function _prepareLayout()    {
            parent::_prepareLayout(); 
            $pager = $this->getLayout()->createBlock("page/html_pager","custom.pager");
            $pager->setAvailableLimit(array(9=>9,15=>15,30=>30,"all"=>"all"));
            $pager->setCollection($this->getCollection());
            $this->setChild("pager",$pager);
            $this->getCollection()->load();
            return $this;
        }

        public function getPagerHtml()   {
            return $this->getChildHtml("pager");
        }

	}
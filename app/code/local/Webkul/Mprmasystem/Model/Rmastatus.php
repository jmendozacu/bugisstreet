<?php
	class Webkul_Mprmasystem_Model_Rmastatus extends Mage_Core_Model_Abstract	{

    	public function setProcessing($id){
    		$model = Mage::getModel("mprmasystem/rmarequest")->load($id);
    		$model->setStatus("Processing")->save();
    	}

    	public function setPending($id){
    		$model = Mage::getModel("mprmasystem/rmarequest")->load($id);
    		$model->setStatus("Pending")->save();
    	}

    	public function setCancelbycustomer($id){
    		$rma = Mage::getModel("mprmasystem/rmarequest")->load($id);
    		$rma->setStatus("Cancelled")->save();
            Mage::getModel("mprmasystem/rmamail")->CancelRMAMailbycustomer($id);
    	}

        public function setCancelbyadmin($id){
            $rma = Mage::getModel("mprmasystem/rmarequest")->load($id);
            if($rma->getStatus() != "Cancelled"){
                $rma->setStatus("Cancelled")->save();
                $seller_id = $rma->getSellerid();
                if($rma->getOrderid()){
                    $order = Mage::getModel("sales/order")->load($rma->getOrderid());
                    if($order->getStatus()=="complete"){
                        $saleslist_coll = Mage::getModel('marketplace/saleslist')->getCollection();
                        $saleslist_coll->addFieldToFilter('mageproid',$rma->getItemid());
                        $saleslist_coll->addFieldToFilter('mageorderid',$rma->getOrderid());
                        foreach ($saleslist_coll as $key) {
                            $id = $key->getAutoid();
                            $remaincancel = $key->getActualparterprocost();
                        }
                        $saleperpartner_coll = Mage::getModel('marketplace/saleperpartner')->getCollection();
                        $saleperpartner_coll->addFieldToFilter('mageuserid',array('eq'=>$seller_id));
                        if(count($saleperpartner_coll)>=1){
                            foreach($saleperpartner_coll as $verifyrow){
                                $totalremain=$verifyrow->getamountremain()-$remaincancel;
                                $verifyrow->setamountremain($totalremain);
                                $verifyrow->save();
                            }
                        }else{
                            $saleperpartner_coll=Mage::getModel('marketplace/saleperpartner');
                            $saleperpartner_coll->setmageuserid($seller_id);
                            $saleperpartner_coll->setamountremain(-$remaincancel);
                            $saleperpartner_coll->save();                       
                        }
                    }else if($order->getStatus()=="processing"||$order->getStatus()=="pending"){
                        $this->removerItemOrder($rma->getItemid(),$rma->getOrderid());
                        $saleslist_coll = Mage::getModel('marketplace/saleslist')->getCollection();
                        $saleslist_coll->addFieldToFilter('mageproid',$rma->getItemid());
                        $saleslist_coll->addFieldToFilter('mageorderid',$rma->getOrderid());
                        foreach ($saleslist_coll as $key) {
                            $id = $key->getAutoid();
                        }
                        Mage::getModel('marketplace/saleslist')->load($id)->delete();                        
                    }
                }
                Mage::getModel("mprmasystem/rmamail")->CancelRMAMailbyadmin($id);
            }
        }

        public function setCancelbyseller($id){
            $rma = Mage::getModel("mprmasystem/rmarequest")->load($id);
            if($rma->getStatus() != "Cancelled"){
                $rma->setStatus("Cancelled")->save();
                $seller_id = $rma->getSellerid();
                if($rma->getOrderid()){
                    $order = Mage::getModel("sales/order")->load($rma->getOrderid());
                    if($order->getStatus()=="complete"){
                        $saleslist_coll = Mage::getModel('marketplace/saleslist')->getCollection();
                        $saleslist_coll->addFieldToFilter('mageproid',$rma->getItemid());
                        $saleslist_coll->addFieldToFilter('mageorderid',$rma->getOrderid());
                        foreach ($saleslist_coll as $key) {
                            $id = $key->getAutoid();
                            $remaincancel = $key->getActualparterprocost();
                        }
                        $saleperpartner_coll = Mage::getModel('marketplace/saleperpartner')->getCollection();
                        $saleperpartner_coll->addFieldToFilter('mageuserid',array('eq'=>$seller_id));
                        if(count($saleperpartner_coll)>=1){
                            foreach($saleperpartner_coll as $verifyrow){
                                $totalremain=$verifyrow->getamountremain()-$remaincancel;
                                $verifyrow->setamountremain($totalremain);
                                $verifyrow->save();
                            }
                        }else{
                            $saleperpartner_coll=Mage::getModel('marketplace/saleperpartner');
                            $saleperpartner_coll->setmageuserid($seller_id);
                            $saleperpartner_coll->setamountremain(-$remaincancel);
                            $saleperpartner_coll->save();                       
                        }
                    }else if($order->getStatus()=="processing"||$order->getStatus()=="pending"){
                        $this->removerItemOrder($rma->getItemid(),$rma->getOrderid());
                        $saleslist_coll = Mage::getModel('marketplace/saleslist')->getCollection();
                        $saleslist_coll->addFieldToFilter('mageproid',$rma->getItemid());
                        $saleslist_coll->addFieldToFilter('mageorderid',$rma->getOrderid());
                        foreach ($saleslist_coll as $key) {
                            $id = $key->getAutoid();
                        }
                        Mage::getModel('marketplace/saleslist')->load($id)->delete();                        
                    }
                }
                Mage::getModel("mprmasystem/rmamail")->CancelRMAMailbyseller($id);
            }
        }

    	public function setSolved($id){
    		$model = Mage::getModel("mprmasystem/rmarequest")->load($id);
           //$model->setUpdatedDate(Mage::getModel('core/date')->date('Y-m-d H:i:s'))->save();
            $model->setUpdatedDate(time())->save();
        	$model->setStatus("Closed")->save();
    	}
    	
        public function removerItemOrder($itemid,$orderid){
            $order = Mage::getModel("sales/order")->load($orderid);
            $base_grand_total = $order->getBaseGrandTotal();
            $base_subtotal = $order->getBaseSubtotal();
            $grand_total = $order->getGrandTotal();
            $subtotal = $order->getSubtotal();

            $base_subtotal_incl_tax = $order->getBaseSubtotalInclTax();
            $subtotal_incl_tax = $order->getSubtotalInclTax();
            $total_item_count = $order->getTotalItemCount();

            $items = $order->getAllItems(); 
            foreach($items as $item){       

                if($item->getParentItemId() == '' || $item->getParentItemId() == null){

                    $product_id = $item->getProductId();
                    if($product_id == $itemid){
                        //remove item price from total price of order
                        $item_price = $item->getPrice();
                        $item->delete();

                        $order->setBaseGrandTotal($base_grand_total-$item_price);
                        $order->setBaseSubtotal($base_subtotal-$item_price);
                            $order->setGrandTotal($grand_total-$item_price);
                        $order->setSubtotal($subtotal-$item_price);

                        $order->setBaseSubtotalInclTax($base_subtotal_incl_tax-$item_price);
                        $order->setSubtotalInclTax($subtotal_incl_tax-$item_price);
                        $order->setTotalItemCount($total_item_count-1);
                        $order->save(); 
                    }

                }

            }
        }
    }
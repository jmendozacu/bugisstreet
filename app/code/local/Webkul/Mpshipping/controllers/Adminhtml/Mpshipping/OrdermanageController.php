<?php
class Webkul_Mpshipping_Adminhtml_Mpshipping_OrdermanageController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('marketplace/marketplace_ordermanage')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Orders Manager'), Mage::helper('adminhtml')->__('Order Manager'));
        
        return $this;
    }   
 
    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
    }
    public function gridAction(){
            $this->loadLayout();
            $this->getResponse()->setBody($this->getLayout()->createBlock("marketplace/adminhtml_ordermanage_grid")->toHtml()); 
        }
   
    public function massStatusAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countOrder = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if($order->getStatus()=='pending'){
                $order->setStatus('processing')->save();
                $countOrder++;

                // for mail to seller start
                $_collection = Mage::getModel('marketplace/saleslist')->getCollection();
                $_collection->addFieldToFilter('mageorderid',$orderId);
                $_collection->addFieldToSelect('mageproownerid')
                            ->distinct(true);
                foreach($_collection as $collection){
                    $fetchsale = Mage::getModel('marketplace/saleslist')->getCollection();
                    $fetchsale->addFieldToFilter('mageorderid',$orderId);   
                    $fetchsale->addFieldToFilter('mageproownerid',$collection->getMageproownerid());
                    $totalprice ='';
                    $orderinfo = '';
                        $style='style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc";';
                        $tax="<tr><td ".$style."><h3>Tax</h3></td><td ".$style."></td><td ".$style."></td><td ".$style."></td></tr><tr>";
                        $options="<tr><td ".$style."><h3>Product Options</h3></td><td ".$style."></td><td ".$style."></td><td ".$style."></td></tr><tr><td ".$style."><b>Options</b></td><td ".$style."><b>Value</b></td><td ".$style."></td><td ".$style."></td></tr>";        
                    foreach($fetchsale as $res){
                        $orderinfo = $orderinfo."<tr>
                                        <td valign='top' align='left' ".$style." >".$res['mageproname']."</td>
                                        <td valign='top' align='left' ".$style.">".Mage::getModel('catalog/product')->load($res['mageproid'])->getSku()."</td>
                                        <td valign='top' align='left' ".$style." >".$res['magequantity']."</td>
                                        <td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($res['mageproprice'])."</td>
                                     </tr>";    
                
                        foreach($order->getAllItems() as $item){
                            if($item->getProductId()==$res['mageproid']){
                                $taxAmount=Mage::app()->getStore()->formatPrice($item->getTaxAmount());
                                $tax=$tax."<tr><td ".$style."><b>Tax Amount</b></td><td ".$style."></td><td ".$style."></td><td ".$style.">".$taxAmount."</td></tr>";
                                $temp=$item->getProductOptions();
                                foreach($temp['options'] as $data){
                                    $optionflag=1;
                                    $options=$options."<tr><td ".$style."><b>".$data['label']."</b></td><td ".$style.">".$data['value']."</td><td ".$style."></td><td ".$style."></td></tr>";
                                    }
                                }
                        }
                        $totalprice = $totalprice+$res['mageproprice'];
                        $userdata = Mage::getModel('customer/customer')->load($res['mageproownerid']);              
                        $Username = $userdata['firstname'];
                        $useremail = $userdata['email'];            
                    }
                    
                    $shipcharge = $order->getShippingAmount();
                    if($item->getTaxAmount()>0){
                        $orderinfo=$orderinfo.$tax;
                    }
                    if($optionflag==1){
                        $orderinfo=$orderinfo.$options;
                    }
                    $orderinfo = $orderinfo."</tbody><tbody><tr>
                                                <td align='right' style='padding:3px 9px' colspan='3'>Grandtotal</td>
                                                <td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($totalprice+$item->getTaxAmount())."</span></td>
                                            </tr>";
                            
                    $billingId = $order->getBillingAddress()->getId();
                    $billaddress = Mage::getModel('sales/order_address')->load($billingId);
                    $billinginfo = $billaddress['firstname'].'<br/>'.$billaddress['street'].'<br/>'.$billaddress['city'].' '.$billaddress['region'].' '.$billaddress['postcode'].'<br/>'.Mage::getModel('directory/country')->load($billaddress['country_id'])->getName().'<br/>T:'.$billaddress['telephone'];  
                    
                    if($order->getShippingAddress()!='')
                        $shippingId = $order->getShippingAddress()->getId();
                    else
                        $shippingId = $billingId;
                    $address = Mage::getModel('sales/order_address')->load($shippingId);                
                    $shippinginfo = $address['firstname'].'<br/>'.$address['street'].'<br/>'.$address['city'].' '.$address['region'].' '.$address['postcode'].'<br/>'.Mage::getModel('directory/country')->load($address['country_id'])->getName().'<br/>T:'.$address['telephone']; 
                    
                    $payment = $order->getPayment()->getMethodInstance()->getTitle();
                    if($order->getShippingAddress()){
                        $shippingId = $order->getShippingAddress()->getId();
                        $address = Mage::getModel('sales/order_address')->load($shippingId);                
                        $shippinginfo = $address['firstname'].'<br/>'.$address['street'].'<br/>'.$address['city'].' '.$address['region'].' '.$address['postcode'].'<br/>'.Mage::getModel('directory/country')->load($address['country_id'])->getName().'<br/>T:'.$address['telephone']; 
                        $shipping = $order->getShippingDescription();   
                        $shippinfo = $shippinginfo;
                        $shippingd = $shipping;     
                    }
                    $emailTemp = Mage::getModel('core/email_template')->loadDefault('webkulorderinvoice');
                    
                    $emailTempVariables = array();              
                    $adminEmail=Mage::getStoreConfig('trans_email/ident_general/email');
                    $adminUsername = 'Admin';
                    $emailTempVariables['myvar1'] = $res['magerealorderid'];
                    $emailTempVariables['myvar2'] = $res['cleared_at'];
                    $emailTempVariables['myvar4'] = $billinginfo;
                    $emailTempVariables['myvar5'] = $payment;
                    $emailTempVariables['myvar6'] = $shippinfo;
                    $emailTempVariables['myvar9'] = $shippingd;
                    $emailTempVariables['myvar8'] = $orderinfo;
                    $emailTempVariables['myvar3'] =$Username;
                    
                    $processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
                    
                    $emailTemp->setSenderName($adminUsername);
                    $emailTemp->setSenderEmail($adminEmail);
                    $emailTemp->send($useremail,$Username,$emailTempVariables);
                }
            }
        }
        if ($countOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been approved from pending status.', $countOrder));
        }
        $this->_redirect('*/*/');
    }
}

<?php
class Webkul_Mpmultishipmethod_Model_Carrier_Mpmultishipping
    extends Mage_Shipping_Model_Carrier_Abstract
    //implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'mp_multi_shipping';

    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request){
        if(!Mage::getStoreConfig('carriers/'.$this->_code.'/active')){
            return false;
        }
        $result = Mage::getModel('shipping/rate_result');
        $handling = 0;
        $session = Mage::getSingleton('checkout/session');
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $postcode=$session->getQuote()->getShippingAddress()->getPostcode();
        $countrycode=$session->getQuote()->getShippingAddress()->getCountry();
        $postcode=str_replace('-', '', $postcode);
        $shippingdetail=array();
        $shippostaldetail=array('countrycode'=>$countrycode,'postalcode'=>$postcode);
        foreach ($session->getQuote()->getAllVisibleItems() as $item) {

            $productowner = Mage::getModel('marketplace/product')->getCollection()
                ->addFieldToFilter('mageproductid', $item->getProductId());

            foreach($productowner as $product){
                $list_shippings = explode( ',', $product->getAllowedShipping());

                Mage::log($product->getUserid(), null, 'sellerdata.log', true);
                foreach($list_shippings as $list){
                    Mage::log($list, null, 'sellerdata.log', true);
                    $method="<label class='mpmultishiplabel'>".$list."</label>";

                }
            }


            $proid=$item->getProductId();
            $options=$item->getProductOptions();
            $mpassignproductId=$options['info_buyRequest']['mpassignproduct_id'];
            if(!$mpassignproductId) {
                foreach($item->getOptions() as $option) {
                    $temp=unserialize($option['value']);
                    if($temp['mpassignproduct_id']) {
                        $mpassignproductId=$temp['mpassignproduct_id'];
                    }
                }
            }
            if($mpassignproductId) {
                $mpassignModel = Mage::getModel('mpassignproduct/mpassignproduct')->load($mpassignproductId);
                $partner = $mpassignModel->getSellerId();
            } else {
                $collection=Mage::getModel('marketplace/product')
                    ->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$proid));
                foreach($collection as $temp) {
                    $partner=$temp->getUserid();
                }
            }
            $product=Mage::getModel('catalog/product')->load($proid)->getWeight();
            $weight=$product*$item->getQty();
            array_push($shippingdetail,array('seller_id'=>$partner,'custom_shipping_method'=>$item->getCustomShippingMethod(),'items_weight'=>$weight,'product_name'=>$item->getName()." X ".$item->getQty(),'qty'=>$item->getQty(),'item_id'=>$item->getId()));

//				if(count($shippingdetail)==0){
//					array_push($shippingdetail,array('seller_id'=>$partner,'items_weight'=>$weight,'product_name'=>$item->getName()." X ".$item->getQty(),'qty'=>$item->getQty(),'item_id'=>$item->getId()));
//				}else{
//					$shipinfoflag=true;
//					$index=0;
//					foreach($shippingdetail as $itemship){
//						if($itemship['seller_id']==$partner){
//							$itemship['items_weight']=$itemship['items_weight']+$weight;
//							$itemship['product_name']=$itemship['product_name'].",".$item->getName()." X ".$item->getQty();
//							$itemship['qty']=$itemship['qty']+$item->getQty();
//							$itemship['item_id']=$itemship['item_id'].",".$item->getId();
//							$shippingdetail[$index]=$itemship;
//							$shipinfoflag=false;
//						}
//						$index++;
//					}
//					if($shipinfoflag==true){
//						array_push($shippingdetail,array('seller_id'=>$partner,'items_weight'=>$weight,'product_name'=>$item->getName()." X ".$item->getQty(),'qty'=>$item->getQty(),'item_id'=>$item->getId()));
//					}
//				}
        }

        $allowedshipping=new Webkul_Mpmultishipmethod_Block_Index();
        $countryId =Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getData('country_id');
       // $specific_country = Mage::getStoreConfig("carriers/mpperproductshipping2/specificcountry");
        //var_dump($specific_country);

        $allowedshipping=$allowedshipping->getAllCarriersList();
        $tmpAllowed = array();
        foreach ($allowedshipping as $ship) {
            $allowcountry = Mage::getStoreConfig("carriers/".$ship['value']."/sallowspecific");
            //var_dump($ship['value'].' : '.$allowcountry);
            if($allowcountry==0){
                $tmpAllowed[] = $ship;
            }
            else{
                $specific_country = Mage::getStoreConfig("carriers/".$ship['value']."/specificcountry");
                //var_dump($countryId,$specific_country,strpos($specific_country, $countryId));
                if(strpos($specific_country, $countryId)!==false){
                    $tmpAllowed[] = $ship;
                }
            }

        }
        //var_dump($tmpAllowed);
        $allowedshipping = $tmpAllowed;
        $shipmethodarr=array(
            'webkulshipping'=>Mage::getModel('mpshipping/carrier_LocalDelivery'),
            'webkulshippingcanadapostal'=>Mage::getModel('mpshippingcanadapostal/carrier_LocalDelivery'),
            'webkulshippingfixrate'=>Mage::getModel('mpshippingfixrate/carrier_LocalDelivery'),
            'mpupsshipping'=>Mage::getModel('mpupsshipping/carrier_LocalDelivery'),
            'mpuspsshipping'=>Mage::getModel('mpuspsshipping/carrier_LocalDelivery'),
            'mpfedexshipping'=>Mage::getModel('mpfedexshipping/carrier_LocalDelivery'),
            'mparamexshipping'=>Mage::getModel('mparamexshipping/carrier_LocalDelivery'),
            'mpperproductshipping'=>Mage::getModel('mpperproductshipping/carrier_LocalDelivery'),
            'mpperproductshipping2'=>Mage::getModel('mpperproductshipping2/carrier_LocalDelivery'),
            'mpperproductshipping3'=>Mage::getModel('mpperproductshipping3/carrier_LocalDelivery'),
            'mpperproductshipping4'=>Mage::getModel('mpperproductshipping4/carrier_LocalDelivery'),
            'mpperproductshipping5'=>Mage::getModel('mpperproductshipping5/carrier_LocalDelivery'),
            'mpperproductshipping6'=>Mage::getModel('mpperproductshipping6/carrier_LocalDelivery'),
            'mpperproductshipping7'=>Mage::getModel('mpperproductshipping7/carrier_LocalDelivery'),
            'mpperproductshipping8'=>Mage::getModel('mpperproductshipping8/carrier_LocalDelivery'),
            'mpperproductshipping9'=>Mage::getModel('mpperproductshipping9/carrier_LocalDelivery'),
            'mpperproductshipping10'=>Mage::getModel('mpperproductshipping10/carrier_LocalDelivery'),
            'mpshippingcorreios'=>Mage::getModel('mpshippingcorreios/carrier_LocalDelivery'),
            'mppercountryperproductshipping'=>Mage::getModel('mppercountryperproductshipping/carrier_LocalDelivery'),
            'mpfastwayshipping'=>Mage::getModel('mpfastwayshipping/carrier_LocalDelivery'),
        );
        $arrMapShipping = array(
            'mpperproductshipping' => 'mp_shipping_charge',
            'mpperproductshipping2' => 'mp_shipping_charge2',
            'mpperproductshipping3' => 'mp_shipping_charge3',
            'mpperproductshipping4' => 'mp_shipping_charge4',
            'mpperproductshipping5' => 'mp_shipping_charge5',
            'mpperproductshipping6' => 'mp_shipping_charge6',
            'mpperproductshipping7' => 'mp_shipping_charge7',
            'mpperproductshipping8' => 'mp_shipping_charge8',
            'mpperproductshipping9' => 'mp_shipping_charge9',
            'mpperproductshipping10' => 'mp_shipping_charge10',
        );
        //var_dump($shippingdetail);
        $shipaccordingtoseller=array();
        $cart = Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();
        //var_dump($cart);
        $arrShipCharge = array();
        foreach($cart as $it){
            //var_dump($it->getData());
            $mpshippingcharge=Mage::getModel('catalog/product')->load($it->getProductId())->getData($arrMapShipping[$it->getCustomShippingMethod()]);
            $arrShipCharge[$it->getItemId()]['name'] = Mage::getStoreConfig("carriers/".$it->getCustomShippingMethod()."/title");
            $arrShipCharge[$it->getItemId()]['method']=$it->getCustomShippingMethod();
            $arrShipCharge[$it->getItemId()]['cost'] = $mpshippingcharge;

        }
        //var_dump($arrShipCharge);
        //Mage::log($arrShipCharge, null, 'sellerdata.log', true);
        foreach($allowedshipping as $shippinginfo){
            $shippingpricedetail=$shipmethodarr[$shippinginfo['value']]->getShippingPricedetail($shippingdetail,$shippostaldetail);

            foreach($shippingpricedetail['shippinginfo'] as $shipdata){
                if(isset($shipaccordingtoseller[$shipdata['seller_id']])){
                    if(isset($shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']])){
                        $submethod=$shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']];
                        foreach($shipdata['submethod'] as $submethoddetail){
                            array_push($submethod,$submethoddetail);
                        }
                        $shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']]=$submethod;
                    }else{
                        $shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']]=$shipdata['submethod'];
                        $shipaccordingtoseller[$shipdata['seller_id']]['products']=$shipdata['product_name'];
                        $shipaccordingtoseller[$shipdata['seller_id']]['item_ids']=$shipdata['item_ids'];
                    }
                }else{
                    //$shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']]=array(array('method'=>'','cost'=>$shipdata['shipping_ammount']));
//						$shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']]=$shipdata['submethod'];
//						$shipaccordingtoseller[$shipdata['seller_id']]['products']=$shipdata['product_name'];
//						$shipaccordingtoseller[$shipdata['seller_id']]['item_ids']=$shipdata['item_ids'];

                    $shipaccordingtoseller[$shipdata['item_ids']][$shipdata['methodcode']]=$shipdata['submethod'];
                    $shipaccordingtoseller[$shipdata['item_ids']]['products']=$shipdata['product_name'];
                    $shipaccordingtoseller[$shipdata['item_ids']]['item_ids']=$shipdata['item_ids'];
                    $shipaccordingtoseller[$shipdata['item_ids']]['seller_id']=$shipdata['seller_id'];
                }
            }
        }
        //var_dump($shipaccordingtoseller[59]);

        $descreption="";
        //var_dump($shipaccordingtoseller[2]);


        /*foreach ($session->getQuote()->getAllVisibleItems() as $item) {
            $proid = $item->getProductId();
            $productowner = Mage::getModel('marketplace/product')->getCollection()
                ->addFieldToFilter('mageproductid', $item->getProductId());
            foreach($productowner as $product){
                $list_shippings = explode( ',', $product->getAllowedShipping());
                Mage::log($list_shippings, null, 'sellerdata.log', true);

            }

        }*/

        foreach($shipaccordingtoseller as $seller_id=>$infoseller){
            //Mage::log($infoseller, null, 'sellerdata.log', true);
            $seller="<div class='seller'><div class='name'>".$infoseller['products']."<div class='products'>".Mage::getModel('customer/customer')->load($infoseller['seller_id'])->getName()."</div></div>";
            $seller=$seller."<input class='selectedshippingname' name='selected_shipping[".$seller_id."][method]' type='hidden' value='".$arrShipCharge[$seller_id]['name']."' />";
            $seller=$seller."<input class='selectedshippingitems' name='selected_shipping[".$seller_id."][items]' type='hidden' value='".$infoseller['item_ids']."' />";
            $seller=$seller."<input class='selectedshippingitems' name='selected_shipping[".$seller_id."][seller_id]' type='hidden' value='".$infoseller['seller_id']."' />";
            $seller=$seller."<input class='selectedshippingamount' name='selected_shipping[".$seller_id."][amount]' type='hidden' value='".$arrShipCharge[$seller_id]['cost']."' />";
            $sellerdata=Mage::getModel('customer/customer')->load($infoseller['seller_id'])->getAllowedShipping();
            $sellerdata=json_decode($sellerdata,true);
            $shipavlflag=false;
            $count = count($infoseller);
            foreach($infoseller as $shipmethod=>$methoddetailarr){
                if(!in_array($shipmethod,$sellerdata)){continue;}
                $script_choose = '';
                if($arrShipCharge[$seller_id]['method']==$shipmethod)
                    $script_choose = " checked='checked' ";
                $shipavlflag=true;
                $methods="<ul class='form-list'>";
                foreach($methoddetailarr as $methoddetail){
                    if($methoddetail['error']==1){continue;}
                    $cost=Mage::helper('core')->currency($methoddetail['cost'], true, false);
                    if($count==1){
                        $method="<li><input type='radio' checked='checked' class='custom_ship' name='".$seller_id."'".$script_choose."value='".$methoddetail['cost']."'></input><label class='mpmultishiplabel'>".$methoddetail['method']." <span class='price'>".$cost."</span></label></li>";
                    }
                    else{
                        /*$method="<li><input type='radio' class='custom_ship' name='".$seller_id."'".$script_choose."value='".$methoddetail['cost']."'></input><label class='mpmultishiplabel'>".$methoddetail['method']." <span class='price'>".$cost."</span></label></li>";*/
                        foreach ($session->getQuote()->getAllVisibleItems() as $item) {
                            $proid = $item->getProductId();
                            $productowner = Mage::getModel('marketplace/product')->getCollection()
                                ->addFieldToFilter('mageproductid', $item->getProductId())
                                ->addFieldToFilter('userid', $infoseller['seller_id']);
                            foreach($productowner as $product){
                                $list_shippings = explode( ',', $product->getAllowedShipping());

                                /*$method="<li><input type='radio' class='custom_ship' name='".$seller_id."'".$script_choose."value='".$methoddetail['cost']."'></input><label class='mpmultishiplabel'>".$list_shippings." <span class='price'>".$cost."</span></label></li>";*/
                            }

                        }
                        //Mage::log($list_shippings, null, 'sellerdata.log', true);

                    }

                    $methods=$methods.$method;
                }
                $methods=$methods."</ul>";
                $seller=$seller.$methods;
            }
            if($shipavlflag===false){
                $seller=$seller."<p class='noshipping' style='color:red'>".Mage::helper('mpmultishipmethod')->__('Sorry, the shipping method selected is not available for the shipping address entered.')."</p>";
            }
            $seller=$seller."</div>";
            $descreption=$descreption.$seller;
        }



//			foreach($shipaccordingtoseller as $seller_id=>$infoseller){
//				$seller="<div class='seller'><div class='name'>".Mage::getModel('customer/customer')->load($seller_id)->getName()."<div class='products'>".$infoseller['products']."</div></div>";
//				$seller=$seller."<input class='selectedshippingname' name='selected_shipping[".$seller_id."][method]' type='hidden' value='' />";
//				$seller=$seller."<input class='selectedshippingitems' name='selected_shipping[".$seller_id."][items]' type='hidden' value='".$infoseller['item_ids']."' />";
//				$seller=$seller."<input class='selectedshippingamount' name='selected_shipping[".$seller_id."][amount]' type='hidden' value='' />";
//				$sellerdata=Mage::getModel('customer/customer')->load($seller_id)->getAllowedShipping();
//				$sellerdata=json_decode($sellerdata,true);
//				$shipavlflag=false;
//				foreach($infoseller as $shipmethod=>$methoddetailarr){
//					if(!in_array($shipmethod,$sellerdata)){continue;}
//
//					$is_custom = 0;
//					switch($shipmethod) {
//						case 'mpperproductshipping':
//							$is_custom = 1;
//							$shipinfo['label_attr'] = 'mp_shipping_charge_title';
//							$shipinfo['label_c'] = Mage::getModel('customer/customer')->load($seller_id)->getData('mp_shipping_charge_title');
//							break;
//						case 'mpperproductshipping2':
//							$is_custom = 1;
//							$shipinfo['label_attr'] = 'mp_shipping_charge_title2';
//							$shipinfo['label_c'] = Mage::getModel('customer/customer')->load($seller_id)->getData('mp_shipping_charge_title2');
//							break;
//						case 'mpperproductshipping3':
//							$is_custom = 1;
//							$shipinfo['label_attr'] = 'mp_shipping_charge_title3';
//							$shipinfo['label_c'] = Mage::getModel('customer/customer')->load($seller_id)->getData('mp_shipping_charge_title3');
//							break;
//					}
//					$shipavlflag=true;
//					$methods="<ul class='form-list'>";
//					foreach($methoddetailarr as $methoddetail){
//						if($methoddetail['error']==1){continue;}
//						$cost=Mage::helper('core')->currency($methoddetail['cost'], true, false);
//						$method="<li><input type='radio' class='custom_ship' name='".$seller_id."' value='".$methoddetail['cost']."'></input><label class='mpmultishiplabel'>".$shipinfo['label_c']." <span class='price'>".$cost."</span></label></li>";
//						$methods=$methods.$method;
//					}
//					$methods=$methods."</ul>";
//					$seller=$seller.$methods;
//				}
//				if($shipavlflag===false){
//					$seller=$seller."<p class='noshipping'>".Mage::helper('mpmultishipmethod')->__('Sorry, no quotes are available for these items at this time from vendor.')."</p>";
//				}
//				$seller=$seller."</div>";
//				$descreption=$descreption.$seller;
//			}

        /*****/
        $shippingamount=0;
        $custm= Mage::getSingleton('core/session')->getMpMultiShippingAmt();
        if($custm!=""){
            $shippingamount=floatval($custm);
        }
        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice($shippingamount);
        $method->setCost($shippingamount);
        $method->setMethodDescription($descreption);
        $result->append($method);
        //echo $descreption;
        //echo ($method->getData('method_description'));die;
        return $result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array($this->_code=>$this->getConfigData('name'));
    }



    public function saveShippingMethod($observer){
        $data=$observer->getRequest();
        $shippingamount=$data->getParam('multicustomship');
        $selected_shipping=$data->getParam('selected_shipping');
        $arr_resort = array();
        $count_seller = array();
        foreach($selected_shipping as $it){
            if(!$count_seller[$it['seller_id']]){
                $count_seller[$it['seller_id']]=0;
            }
            if($count_seller[$it['seller_id']]==0){
                $arr_resort[$it['seller_id']] = array('method'=>$it['method'],
                    'items' => $it['items'],
                    'amount' => $it['amount'] );
            }else{
                $arr_resort[$it['seller_id']]['method'].=','.$it['method'];
                $arr_resort[$it['seller_id']]['items'].=','.$it['items'];
                $arr_resort[$it['seller_id']]['amount']+=$it['amount'];
            }
            $count_seller[$it['seller_id']]++;
        }
        //var_dump($arr_resort,$count_seller,$data->getParams());die;
        $quote=$observer->getQuote();
        $data=$quote->getShippingAddress()->getAllShippingRates();
        foreach($data as $_rate){
            if($_rate->getCode()=='mp_multi_shipping_mp_multi_shipping'){
                $_rate->setPrice($shippingamount);
                $_rate->save();
                Mage::getSingleton('core/session')->setMpMultiShippingAmt($shippingamount);
                Mage::getSingleton('core/session')->setSelectedShipping($arr_resort);
                $quote->save();
            }
        }
    }

    public function afterPlaceOrder($observer) {
        $lastOrderId=$observer->getOrderIds();
        $order = Mage::getModel('sales/order')->load($lastOrderId);
        $lastOrderId=$order->getId();
        $shippingmethod=$order->getShippingMethod();
        $allorderitems=$order->getAllItems();
        $des = ($order->getShippingDescription());
        if(strpos($shippingmethod,'mp_multi_shipping')!==false){
            $shippingAll=Mage::getSingleton('core/session')->getData('selected_shipping');
            $custom_shipping_txt = ' ';//to save custom shipping info to orders - Phuc
            foreach($shippingAll as $sellerid=>$shipdata){
                $_seller = Mage::getModel('customer/customer')->load($sellerid);
                $custom_shipping_txt .= '<p class="'.$_seller->getId().'">';
                $items=explode(',',$shipdata['items']);
                $shipdata['items']="";
                foreach($allorderitems as $item){
                    if(in_array($item->getQuoteItemId(),$items)){
                        $shipdata['items']=$shipdata['items'].$item->getItemId().",";
                    }
                }
                $custom_shipping_txt .= ' '.$_seller->getFirstname().' '.$_seller->getLastname().' : '.$shipdata['method'].'</p>';
                $data=array(
                    'order_id'=>$lastOrderId,
                    'item_ids'=>$shipdata['items'],
                    'seller_id'=>$sellerid,
                    'carrier_name'=>$shipdata['method'],
                    'shipping_charges'=>$shipdata['amount']
                );
                $collectiont=Mage::getModel('mpshippingmanager/tracking');
                $collectiont->setData($data);
                $collectiont->save();
            }
            $custom_shipping_txt.= ' ';
            $des.=$custom_shipping_txt;
            $order->setShippingDescription($des);
            $order->save();
            $order->sendNewOrderEmail();
            Mage::getSingleton('core/session')->unsetSelectedShipping();
        }
    }
}

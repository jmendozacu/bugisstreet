<?php class Webkul_Marketplace_Model_Userprofile extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('marketplace/userprofile');
    }

    public function getRegisterDetail($customer)
    {
        $data = Mage::getSingleton('core/app')->getRequest();
        $wholedata = $data->getParams();
        if ($wholedata['wantpartner'] == 1) {
            foreach ($customer->getAddresses() as $address) {
                $customerAddress = $address->toArray();
            }
            $status = Mage::getStoreConfig('marketplace/marketplace_options/partner_approval') ? 0 : 1;
            $assinstatus = Mage::getStoreConfig('marketplace/marketplace_options/partner_approval') ? "Pending" : "Seller";
            $customerid = $customer->getId();
            $collection = Mage::getModel('marketplace/userprofile');
            $collection->setwantpartner($status);
            $collection->setpartnerstatus($assinstatus);
            $collection->setmageuserid($customerid);
            $collection->setContactnumber($customerAddress['telephone']);
            $collection->setProfileurl($wholedata['profileurl']);
            $collection->save();
        }
    }

    public function massispartner($data)
    {
        $wholedata = $data->getParams();
        $groups2 = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', '4')
            ->load();
        foreach ($groups2 as $group2) {
            $limit2 = $group2['transaction_percent'];
        }
        foreach ($wholedata['customer'] as $key) {
            $sellerid = $key;
            $collection = Mage::getModel('marketplace/userprofile')->getCollection()->addFieldToFilter('mageuserid', array('eq' => $key));
            foreach ($collection as $row) {
                $auto = $row->getautoid();
                $want = $row->getwantpartner();
                $collection1 = Mage::getModel('marketplace/userprofile')->load($auto);
                if ($want == 0) {
                    $mage = $row->getmageuserid();
                    $customer = Mage::getModel('customer/customer')->load($mage);
                    $customerno = $customer->getContactno();
                    $sellername = $customer->getShopname();
                    $selldes = $customer->getShopdescription();
                    $countryno = $customer->getCountryphone();
                    $areacode = $customer->getAreacode();

                    $selldesf = '<div>' . $selldes . '</div>';
                    $collection1->setshoptitle($sellername);
                    $collection1->setcompdesi($selldesf);
                    $collection1->setcontactnumber($customerno);
                    $collection1->setareamobile($areacode);
                    $collection1->setcountrymobile($countryno);
                    $collection1->setmember("4");

                    $collectionselect = Mage::getModel('marketplace/saleperpartner')->getCollection();
                    $collectionselect->addFieldToFilter('mageuserid', array('eq' => $key));
                    if (count($collectionselect) == 1) {
                        foreach ($collectionselect as $verifyrow) {
                            $autoid = $verifyrow->getautoid();
                        }

                        $collectionupdate = Mage::getModel('marketplace/saleperpartner')->load($autoid);
                        $collectionupdate->setcommision($limit2);
                        $collectionupdate->save();
                    } else {
                        $collectioninsert = Mage::getModel('marketplace/saleperpartner');
                        $collectioninsert->setmageuserid($customer->getId());
                        $collectioninsert->setcommision($limit2);
                        $collectioninsert->save();
                    }
                }
                $collection1->setwantpartner(1);
                $collection1->setpartnerstatus('Seller');
                $collection1->save();
            }
            $users = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('userid', array('eq' => $sellerid));
            foreach ($users as $value) {
                $allStores = Mage::app()->getStores();
                foreach ($allStores as $_eachStoreId => $val) {
                    Mage::getModel('catalog/product_status')->updateProductStatus($value->getMageproductid(), Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                }
                $value->setStatus(1);
                $value->save();
            }
            $customer = Mage::getModel('customer/customer')->load($key);
            $emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerapprove');

            $emailTempVariables = array();
            $admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
            $adminEmail = $admin_storemail ? $admin_storemail : Mage::getStoreConfig('trans_email/ident_general/email');
            $adminUsername = 'Admin';
            $emailTempVariables['myvar1'] = $customer->getName();
            $emailTempVariables['myvar2'] = Mage::helper('customer')->getLoginUrl();

            $processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);

            $emailTemp->setSenderName($adminUsername);
            $emailTemp->setSenderEmail($adminEmail);
            $emailTemp->send($customer->getEmail(), $adminUsername, $emailTempVariables);
            Mage::dispatchEvent('mp_approve_seller', array('seller' => $customer));
        }
    }

    public function massisnotpartner($data)
    {
        $wholedata = $data->getParams();
        foreach ($wholedata['customer'] as $key) {
            $sellerid = $key;
            $collection = Mage::getModel('marketplace/userprofile')->getCollection();
            $collection->getSelect()->where('mageuserid =' . $key);
            foreach ($collection as $row) {
                $auto = $row->getautoid();
                $collection1 = Mage::getModel('marketplace/userprofile')->load($auto);
                $collection1->setwantpartner(0);
                $collection1->setpartnerstatus('Default User');
                $collection1->save();
            }
            $users = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('userid', array('eq' => $sellerid));
            foreach ($users as $value) {
                $id = $value->getMageproductid();
                $magentoProductModel = Mage::getModel('catalog/product')->load($id);
                $allStores = Mage::app()->getStores();
                foreach ($allStores as $_eachStoreId => $val) {
                    Mage::getModel('catalog/product_status')->updateProductStatus($value->getMageproductid(), Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                }
                $magentoProductModel->setStatus(2)->save();
                $value->setStatus(2);
                $value->save();
            }
            $customer = Mage::getModel('customer/customer')->load($key);
            $emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerdisapprove');
            $emailTempVariables = array();
            $admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
            $adminEmail = $admin_storemail ? $admin_storemail : Mage::getStoreConfig('trans_email/ident_general/email');
            $adminUsername = 'Admin';
            $emailTempVariables['myvar1'] = $customer->getName();
            $emailTempVariables['myvar2'] = Mage::helper('customer')->getLoginUrl();

            $processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);

            $emailTemp->setSenderName($adminUsername);
            $emailTemp->setSenderEmail($adminEmail);
            $emailTemp->send($customer->getEmail(), $adminUsername, $emailTempVariables);
            Mage::dispatchEvent('mp_disapprove_seller', array('seller' => $customer));

        }
    }


    public function massprintstate($data)
    {
        if (!headers_sent()) {
            header('Content-Type: application/x-excel');
            header('Content-Disposition: attachment; filename="Vendor_Statement.csv"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            echo(implode(',', array('Vendor Statement')));
            echo("\n");
            echo(implode(',', array('')));
            echo("\n");


            ini_set("date.timezone", "Asia/Singapore");
        }
        $wholedata = $data->getParams();
        foreach ($wholedata["customer"] as $key => $value) {
            $collection1 = Mage::getModel('marketplace/userprofile')->getPartnerProfileById($value);
            $want = $collection1["wantpartner"];
            if ($want == 1) {
                $title = $collection1['shoptitle'];
                $groups = Mage::getResourceModel('customer/group_collection')
                    ->addFieldToFilter('customer_group_id', $collection1['member'])
                    ->load();
                foreach ($groups as $group) {
                    $type = $group['customer_group_code'];
                }


                echo(implode(',', array('', 'Date From', 'Date To', 'Vendor ID', 'Vendor', 'Membership Type', 'Created At')));
                echo("\n");
                echo(implode(',', array('', Mage::getModel('core/date')->date('d/m/Y', strtotime($wholedata['date_from'])) . " " . "0:00", Mage::getModel('core/date')->date('d/m/Y', strtotime($wholedata['date_to'])) . " " . "0:00", $value, $title, $type, Mage::getModel('core/date')->date('d/m/Y H:i:s'))));
                echo("\n");
                echo(implode(',', array('Order Number', 'Invoice Number', 'Order Date', 'Order Completion Date', 'Amount', 'Payment Method', 'Status', 'Remark')));
                echo("\n");
                $from_date = new DateTime($wholedata['date_from']);
                $to_date = new DateTime($wholedata['date_to']);

                if ($from_date < $to_date) {
                    $customerid = $value;
                    $collection = Mage::getModel('marketplace/saleslist')->getCollection();
                    $collection->addFieldToFilter('mageproownerid', array('eq' => $customerid));
                    $collection->addFieldToFilter('magerealorderid', array('neq' => 0));
                    $collection->addFieldToFilter('cpprostatus', 1);
                    $prefix = Mage::getConfig()->getTablePrefix();
                    $collection->getSelect()
                        ->join(array("ccp" => $prefix . "sales_flat_order"), "ccp.entity_id = main_table.mageorderid", array("status" => "status"))
                        ->join(array("ccp2" => $prefix . "sales_flat_order_item"), "ccp2.product_id = main_table.mageproid AND ccp2.order_id = main_table.mageorderid", array("item_id" => "item_id"));
                    $collection->getSelect()->group('entity_id');

                    $this->setCollection($collection);
                    $array = array();
                    $test = "0";
                    $array1 = array();
                    $total_amount = 0;
                    $count = count($collection) - 1;
                    $i = 0;
                    $orderComments = "";
                    foreach ($collection as $item) {

                        $id = $item["magerealorderid"];
                        $order = Mage::getModel('sales/order')->loadByIncrementId($id);
                        $orderdate = Mage::helper('core')->formatDate($order->getUpdatedAt(), 'short', true);
                        $orderdate = new DateTime($orderdate);
                        if ($from_date <= $orderdate && $orderdate < $to_date) {
                            if ($test == $item["magerealorderid"]) {
                                $array1["amount"] = $order->getBaseGrandTotal();
                            } else {
                                $array1 = array();
                                $test = $id;
                                if ($order->getPayment()) {
                                    $paymentCode = $order->getPayment()->getMethod();
                                    $payment = $order->getPayment();
                                    $sType = $payment->getCcType();
                                    if ($sType == null) {
                                        $payment_method = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
                                    } else {
                                        $aType = Mage::getSingleton('payment/config')->getCcTypes();
                                        if (isset($aType[$sType])) {
                                            $payment_method = $aType[$sType];
                                        } else {
                                            $payment_method = Mage::helper('payment')->__('N/A');
                                        }
                                    }

                                }
                                $array1["ordernumber"] = $id;
                                $item_invoice = 0;
                                $invoice_id = 0;
                                $invoice = Mage::getResourceModel('sales/order_invoice_item_collection')
                                    ->addAttributeToSelect('*')
                                    ->addAttributeToFilter('order_item_id', array('eq' => $item->getItemId()))
                                    ->addAttributeToFilter('product_id', array('eq' => $item->getMageproid()))
                                    ->load();
                                foreach ($invoice as $value) {
                                    $item_invoice = 1;

                                    $invoice_id = $value["parent_id"];

                                }
                                $fromDate = $order->getCreatedAtStoreDate();
                                $formattedDate = Mage::getModel('core/date')
                                    ->date('Y', strtotime($fromDate));

                                if ($invoice_id != "0") {
                                    $check = Mage::getModel("sales/order_invoice")->load($invoice_id);

                                    $array1["invoice"] = "S/" . $check->getIncrementId() . "/" . $formattedDate;

                                } else {
                                    $array1["invoice"] = "";
                                }
                                $array1["orderdate"] = Mage::getModel('core/date')->date('d/m/Y H:i:s', $order->getCreatedAt());
                                $array1["completiondate"] = Mage::getModel('core/date')->date('d/m/Y H:i:s', $order->getUpdatedAt());

                                $array1["amount"] = $check->getBaseGrandTotal();

                                $array1["payment"] = $payment_method;
                                $array1["type"] = $order["status"];

                                $orderComments = $order->getAllStatusHistory();


                                $total_amount = $total_amount + (float)$check->getBaseGrandTotal();

                                $flag = 0;
                                foreach ($orderComments as $comment) {

                                    $body = $comment->getData('comment');
                                    if ($body != null) {
                                        $flag++;
                                        $final = "Status: " . $comment->getData('status') . "- Comment: " . $body;
                                        if ($flag == 1) {
                                            $array1["remark"] = $final;
                                            echo(implode(',', $array1));
                                            echo("\n");
                                        } else {
                                            echo(implode(',', array('', '', '', '', '', '', '', $final)));
                                            echo("\n");
                                        }


                                    }
                                }
                                if ($flag == 0) {
                                    echo(implode(',', $array1));
                                    echo("\n");
                                }
                                //}

                            }


                            $i++;
                        }

                    }

                } else {
                    $array = array();
                    $total_amount = 0;
                }

                echo(implode(',', array('', '', '', 'Total', 'SGD ' . $total_amount)));
                echo("\n");
                echo(implode(',', array('')));
                echo("\n");

            }

        }


        die();
    }

    public function massprintrefund($data)
    {
        try {

            if (!headers_sent()) {
                header('Content-Type: application/x-excel, charset=utf-8');
                header('Content-Disposition: attachment; filename="Refund_Listing.csv"');
                header('Expires: 0');
                header("Cache-Control: private", false);
                header('Pragma: public');


            }
            $wholedata = $data->getParams();


            foreach ($wholedata["customer"] as $key => $value) {
                try {
                    $collection1 = Mage::getModel('marketplace/userprofile')->getPartnerProfileById($value);
                    $want = $collection1["wantpartner"];
                    if ($want == 1) {
                        $title = $collection1['shoptitle'];


                        echo(implode(',', array('', 'Date From', 'Date To', 'Vendor ID', 'Vendor', 'Created At')));
                        echo("\n");
                        echo(implode(',', array(Mage::getModel('core/date')->date('d/m/Y', strtotime($wholedata['date_from'])) . " " . "0:00", Mage::getModel('core/date')->date('d/m/Y', strtotime($wholedata['date_to'])) . " " . "0:00", $value, $title, Mage::getModel('core/date')->date('d/m/Y H:i:s'))));
                        echo("\n");
                        echo(implode(',', array('Order Number', 'Invoice Number', 'Order Date', 'Refund Date', 'Refund Closed Date')));
                        echo("\n");
                        try {

                            $from_date = new DateTime($wholedata['date_from']);
                            $to_date = new DateTime($wholedata['date_to']);


                        } //end try - start catch
                        catch (Exception $e) {

                        }


                        if ($from_date < $to_date) {
                            $customerid = $value;
                            $collection = Mage::getModel('marketplace/saleslist')->getCollection();
                            $collection->addFieldToFilter('mageproownerid', array('eq' => $customerid));
                            // $collection->addFieldToFilter('magerealorderid', array('neq' => 0));
                            $prefix = Mage::getConfig()->getTablePrefix();
                            $collection->getSelect()
                                ->join(array("ccp" => $prefix . "sales_flat_order"), "ccp.entity_id = main_table.mageorderid", array("status" => "status"))
                                ->join(array("ccp2" => $prefix . "sales_flat_order_item"), "ccp2.product_id = main_table.mageproid AND ccp2.order_id = main_table.mageorderid", array("item_id" => "item_id"));
                            $collection->getSelect()->group('entity_id');
                            $collection->addFieldToFilter('status', 'complete');
                            $this->setCollection($collection);
                            $array = array();
                            $test = "0";
                            $before = "0";
                            $payment_method = "";

                            $array1 = array();

                            $count = count($collection) - 1;

                            $i = 0;
                            foreach ($collection as $item) {
                                $order_status = $item['status'];
                                if ($order_status != "canceled") {


                                    if ($test == $item["magerealorderid"]) {

                                    } else {
                                        try {
                                            if (count($array1) != 0) {

                                                $rma = Mage::getResourceModel("mprmasystem/rmarequest_collection")
                                                    ->addFieldToFilter("orderid", $before);

                                                foreach ($rma as $row1) {
                                                    $rmadate = Mage::helper('core')->formatDate($row1['updated_date'], 'short', true);
                                                    $rmadate = new DateTime($rmadate);

                                                    // Refund Date

                                                    if ($row1['status'] == "Closed" && $row1['sellerstatus'] == 2) {
                                                        if ($from_date <= $rmadate && $rmadate <= $to_date) {
                                                            $rmaid = $row1['id'];
                                                            $collect3 = Mage::getResourceModel("mprmasystem/conversation_collection")->addFieldToFilter("rmaid", $rmaid);
                                                            $array2 = $array1;
                                                            $array2['refund_date'] = Mage::getModel('core/date')->date('d/m/Y H:i:s', strtotime($row1['created_at']));
                                                            $array2["updated"] = Mage::getModel('core/date')->date('d/m/Y H:i:s', strtotime($row1['updated_date']));
                                                            echo(implode(',', $array2));
                                                            echo("\n");
                                                        }
                                                    }
                                                }
                                                $array[] = $array1;
                                            }
                                            $array1 = array();
                                            $id = $item["magerealorderid"];
                                            $test = $id;
                                            $be = $item["mageorderid"];
                                            $before = $be;
                                            $order = Mage::getModel('sales/order')->loadByIncrementId($id);
                                            if ($order->getPayment()) {
                                                $payment_method = $order->getPayment()->getMethodInstance()->getTitle();
                                            }

                                            $array1["ordernumber"] = $id;
                                            $item_invoice = 0;
                                            $invoice_id = 0;
                                            $invoice = Mage::getResourceModel('sales/order_invoice_item_collection')
                                                ->addAttributeToSelect('*')
                                                ->addAttributeToFilter('order_item_id', array('eq' => $item->getItemId()))
                                                ->addAttributeToFilter('product_id', array('eq' => $item->getMageproid()))
                                                ->load();
                                            foreach ($invoice as $value) {
                                                $item_invoice = 1;
                                                $invoice_id = $value["parent_id"];
                                            }

                                            $fromDate = $order->getCreatedAtStoreDate();
                                            $formattedDate = Mage::getModel('core/date')
                                                ->date('Y', strtotime($fromDate));
                                            try {
                                                if ($invoice_id != "0") {
                                                    $check = Mage::getModel("sales/order_invoice")->load($invoice_id);
                                                    $array1["invoice"] = "R/" . $check->getIncrementId() . "/" . $formattedDate;
                                                    //$array1["invoice"] = "R/".$id."/".substr($order->getTotalQtyOrdered(),0,-5)."/".$formattedDate;
                                                } else {
                                                    $array1["invoice"] = "";
                                                }
                                            } catch
                                            (Exception $e) {

                                            }

                                            $array1["orderdate"] = Mage::getModel('core/date')->date('d/m/Y H:i:s', strtotime($order->getCreatedAt()));
                                        } //end try - start catch
                                        catch (Exception $e) {

                                        }

                                    }
                                    if ($i == $count) {
                                        try {
                                            $rma = Mage::getResourceModel("mprmasystem/rmarequest_collection")
                                                ->addFieldToFilter("orderid", $before);
                                            foreach ($rma as $row1) {
                                                //$rmadate = new DateTime($row1['seller_update']);
                                                $rmadate = Mage::helper('core')->formatDate($row1['updated_date'], 'short', true);
                                                $rmadate = new DateTime($rmadate);

                                                if ($row1['status'] == "Closed" && $row1['sellerstatus'] == 2) {
                                                    if ($from_date <= $rmadate && $rmadate <= $to_date) {
                                                        $rmaid = $row1['id'];
                                                        //  $collect3 = Mage::getResourceModel("mprmasystem/conversation_collection")->addFieldToFilter("rmaid", $rmaid);
                                                        $array2 = $array1;
                                                        $array2['refund_date'] = Mage::getModel('core/date')->date('d/m/Y H:i:s', strtotime($row1['created_at']));
                                                        $array2["updated"] = Mage::getModel('core/date')->date('d/m/Y H:i:s', strtotime($row1['updated_date']));
                                                        echo(implode(',', $array2));
                                                        echo("\n");
                                                    }
                                                }

                                            }

                                        } //end try - start catch
                                        catch (Exception $e) {

                                        }

                                    }
                                    $i++;
                                }
                            }// end - foreach

                        } else {
                            $array = array();
                        }
                        echo(implode(',', array('')));
                        echo("\n");

                    }
                } //end try - start catch
                catch
                (Exception $e) {
                    echo " - Error is: " . $e->getMessage();
                }
            } // end - foreach


        } //end try - start catch
        catch (Exception $e) {

        }


    }


    public
    function denypartner($data)
    {
        $wholedata = $data->getParams();
        $sellerid = $wholedata['sellerid'];
        $collection = Mage::getModel('marketplace/userprofile')->getCollection()
            ->addFieldToFilter('mageuserid', array('eq' => $sellerid));
        foreach ($collection as $row) {
            $auto = $row->getautoid();
            $collection1 = Mage::getModel('marketplace/userprofile')->load($auto);
            $collection1->setwantpartner(0);
            $collection1->setpartnerstatus('Default User');
            $collection1->save();
        }
        $users = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('userid', array('eq' => $sellerid));
        foreach ($users as $value) {
            $id = $value->getMageproductid();
            $magentoProductModel = Mage::getModel('catalog/product')->load($id);
            $allStores = Mage::app()->getStores();
            foreach ($allStores as $_eachStoreId => $val) {
                Mage::getModel('catalog/product_status')->updateProductStatus($value->getMageproductid(), Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            }
            $magentoProductModel->setStatus(2)->save();
            $value->setStatus(2);
            $value->save();
        }
        $customer = Mage::getModel('customer/customer')->load($sellerid);
        $emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerdeny');
        $emailTempVariables = array();
        $admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
        $adminEmail = $admin_storemail ? $admin_storemail : Mage::getStoreConfig('trans_email/ident_general/email');
        $adminUsername = 'Admin';
        $emailTempVariables['myvar1'] = $customer->getName();
        $emailTempVariables['myvar2'] = $wholedata['seller_deny_reason'];

        $processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);

        $emailTemp->setSenderName($adminUsername);
        $emailTemp->setSenderEmail($adminEmail);
        $emailTemp->send($customer->getEmail(), $adminUsername, $emailTempVariables);
        return $customer->getName();
    }

    public
    function getPartnerProfileById($partnerId)
    {
        $data = array();
        if ($partnerId != '') {
            $collection = Mage::getModel('marketplace/userprofile')->getCollection();
            $collection->addFieldToFilter('mageuserid', array('eq' => $partnerId));
            $user = Mage::getModel('customer/customer')->load($partnerId);
            $name = explode(' ', $user->getName());
            foreach ($collection as $record) {
                $bannerpic = $record->getbannerpic();
                $logopic = $record->getlogopic();
                $countrylogopic = $record->getcountrypic();
                if (strlen($bannerpic) <= 0) {
                    $bannerpic = 'banner-image.png';
                }
                if (strlen($logopic) <= 0) {
                    $logopic = 'noimage.png';
                }
                if (strlen($countrylogopic) <= 0) {
                    $countrylogopic = '';
                }
                $data = array(
                    'firstname' => $name[0],
                    'lastname' => $name[1],
                    'sellerid' => $record->getmageuserid(),
                    'email' => $user->getEmail(),
                    'twitterid' => $record->gettwitterid(),
                    'facebookid' => $record->getfacebookid(),
                    'contactnumber' => $record->getcontactnumber(),
                    'bannerpic' => $bannerpic,
                    'logopic' => $logopic,
                    'complocality' => $record->getcomplocality(),
                    'countrypic' => $countrylogopic,
                    'meta_keyword' => $record->getMetaKeyword(),
                    'meta_description' => $record->getMetaDescription(),
                    'compdesi' => $record->getcompdesi(),
                    'returnpolicy' => $record->getReturnpolicy(),
                    'shippingpolicy' => $record->getShippingpolicy(),
                    'profileurl' => $record->getProfileurl(),
                    'shoptitle' => $record->getShoptitle(),
                    'backgroundth' => $record->getbackgroundth(),
                    'wantpartner' => $record->getwantpartner(),
                    'hide' => $record->gethide(),
                    'areamobile' => $record->getareamobile(),
                    'countrymobile' => $record->getcountrymobile(),
                    'member' => $record->getmember()
                );
            }
            return $data;
        }
    }


    public
    function isPartner()
    {
        $partnerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($partnerId == '')
            $partnerId = Mage::registry('current_customer')->getId();
        if ($partnerId != '') {
            $data = '';
            $collection = Mage::getModel('marketplace/userprofile')->getCollection();
            $collection->addFieldToFilter('mageuserid', array('eq' => $partnerId));
            foreach ($collection as $record) {
                $data = $record->getwantpartner();
            }
            return $data;
        }
    }

    public
    function isRightSeller($productid)
    {
        $customerid = Mage::getSingleton('customer/session')->getCustomerId();
        $data = 0;
        $product = Mage::getModel('marketplace/product')->getCollection()
            ->addFieldToFilter('userid', array('eq' => $customerid))
            ->addFieldToFilter('mageproductid', array('eq' => $productid));
        foreach ($product as $record) {
            $data = 1;
        }
        return $data;
    }

    public
    function getpaymentmode()
    {
        $partnerId = Mage::registry('current_customer')->getId();
        $collection = Mage::getModel('marketplace/userprofile')->getCollection();
        $collection->addFieldToFilter('mageuserid', array('eq' => $partnerId));
        foreach ($collection as $record) {
            $data = $record->getPaymentsource();
        }
        return $data;
    }

    public
    function assignProduct($customer, $sid)
    {
        $productids = explode(',', $sid);
        foreach ($productids as $proid) {
            $userid = '';
            $product = Mage::getModel('catalog/product')->load($proid);
            if ($product->getname()) {
                $collection = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('mageproductid', array('eq' => $proid));
                foreach ($collection as $coll) {
                    $userid = $coll['userid'];
                }
                if ($userid) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('The product is already assign to other seller.'));
                } else {
                    $collection1 = Mage::getModel('marketplace/product');
                    $collection1->setMageproductid($proid);
                    $collection1->setUserid($customer->getId());
                    $collection1->setStatus($product->getStatus());
                    $collection1->setAdminassign(1);
                    $collection1->setWebsiteIds(array(Mage::app()->getStore()->getStoreId()));
                    $collection1->save();

                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Products has been successfully assigned to seller.'));
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('adminhtml')->__("Product with id") . " " . $proid . " " . Mage::helper('adminhtml')->__("doesn't exist."));
            }
        }
    }

    public
    function unassignProduct($customer, $sid)
    {
        $productids = explode(',', $sid);
        foreach ($productids as $proid) {
            $userid = '';
            $product = Mage::getModel('catalog/product')->load($proid);
            if ($product->getname()) {
                $collection = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('mageproductid', array('eq' => $proid));
                foreach ($collection as $coll) {
                    $coll->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Products has been successfully unassigned to seller.'));
            } else {
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('adminhtml')->__("Product with id") . " " . $proid . " " . Mage::helper('adminhtml')->__("doesn't exist."));
            }
        }
    }

    public
    function isCustomerProducttemp($magentoProductId)
    {
        $collection = Mage::getModel('marketplace/product')->getCollection();
        $collection->addFieldToFilter('mageproductid', array('eq' => $magentoProductId));
        $userid = '';
        foreach ($collection as $record) {
            $userid = $record->getuserid();
        }
        $collection1 = Mage::getModel('marketplace/userprofile')->getCollection()->addFieldToFilter('mageuserid', array('eq' => $userid));
        foreach ($collection1 as $record1) {
            $status = $record1->getWantpartner();
        }
        if ($status != 1) {
            $userid = '';
        }
        return array('productid' => $magentoProductId, 'userid' => $userid);
    }
}
<?php
class MW_Publicbank_SubmitformController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
    {
        //var_dump(Mage::getStoreConfig('payment/publicbank_standard/is_test'));
        if(Mage::getStoreConfig('payment/publicbank_standard/is_test')=='1')
            $sub_url = 'https://test.wirecard.com.sg/easypay2/paymentpage.do?';
        else
            $sub_url = 'https://securepayments.telemoneyworld.com/easypay2/paymentpage.do?';
        $arrOrder = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId())->toArray();
        $arrCredit = Mage::getSingleton('core/session')->getCredit();
        $order_id = str_pad($arrOrder['increment_id'], 20, "0", STR_PAD_LEFT);
        $expired_date = substr($arrCredit['cc_exp_year'],2,2).str_pad($arrCredit['cc_exp_month'], 2, "0", STR_PAD_LEFT);
        $amount = floatval($arrOrder['grand_total']);//str_pad($arrOrder['grand_total']*100, 12, "0", STR_PAD_LEFT);
        $merchant_id = Mage::getStoreConfig('payment/publicbank_standard/merchant_vi_id');
        //var_dump(Mage::getSingleton('checkout/session')->getLastOrderId(),$order_id,$expired_date);
        if($arrCredit['cc_type']=='VI'){
            $paytype=3;
        }
        elseif($arrCredit['cc_type']=='MC')
        {
            $paytype=2;
        }
        else{
            $paytype=41;
        }
        $cur = 'SGD';
        $submit_form = '<html>
		<head><title>Router to ACS</title></head>
		<body OnLoad="OnLoadEvent();" >
        <form id="Form1" name="submitForm" action="'.$sub_url.'" method="get">
            <TABLE id="Table1" cellSpacing="0" cellPadding="3" width="100%" border="0" style="display:none">
            <TR>
                <TD align="right" width="30%">Pay To:</TD>
                 <TD><INPUT id="mid" type="text" value="'.$merchant_id.'" name="mid"></TD>
            </TR>';
        if($paytype!=41){
        $submit_form .=  '<TR>
                <TD align="right" width="30%">ccnum :</TD>
                 <TD><INPUT id="ccnum" type="text" value="'.$arrCredit['cc_number'].'" name="ccnum"></TD>
            </TR>
            <TR>
                <TD align="right" width="30%">expiryDate:</TD>
                 <TD><INPUT id="ccdate" type="text" value="'.$expired_date.'" name="ccdate"></TD>
            </TR>
            <TR>
                <TD align="right" width="30%">CVV2:</TD>
                 <TD><INPUT id="cccvv" type="text" value="'.$arrCredit['cc_cid'].'" name="cccvv"></TD>
            </TR>
            <TR>
                <TD align="right" width="30%">Name:</TD>
                 <TD><INPUT id="ccname" type="text" value="'.$arrCredit['cc_owner'].'" name="ccname"></TD>
            </TR>';
        }
        $submit_form .=  '<TR>
                <TD align="right" width="30%">paytype :</TD>
                 <TD><INPUT id="paytype" type="text" value="'.$paytype.'" name="paytype"></TD>
            </TR>
            <TR>
                <TD align="right">Invoice No:</TD>
                <TD><INPUT id="ref" type="text" value="'.$order_id.'" name="ref"></TD>
            </TR>
            <TR>
                <TD align="right">Amount:</TD>
                <TD><INPUT id="amt" type="text" value="'.$amount.'" name="amt"></TD>
            </TR>
            <TR>
                <TD align="right">Currency:</TD>
                <TD><INPUT id="cur" type="text" value="'.$cur.'" name="cur"></TD>
            </TR>
            <TR>
                <TD align="right">Auto Direct:</TD>
                <TD><INPUT id="skipstatuspage" type="text" value="N" name="skipstatuspage"></TD>
            </TR>
            <TR>
                <TD align="right">Post URL (card holder will see this page):</TD>
                <TD><INPUT id="statusurl" type="text" value="'.Mage::getUrl('publicbank/returnurl/index', array('_secure' => true)).'" name="statusurl"></TD>
            </TR>
            <TR>
                <TD align="right">Return URL (card holder will see this page):</TD>
                <TD><INPUT id="postURL" type="text" value="'.Mage::getUrl('publicbank/returnurl/return', array('_secure' => true)).'" name="returnurl"></TD>
            </TR>
            <TR>
                <TD align="right">&nbsp;</TD>
                <TD><INPUT type="submit" value="Submit"></TD>
            </TR>
            </TABLE>
        </form>
        <SCRIPT LANGUAGE="Javascript" >
		    function OnLoadEvent(){ document.submitForm.submit(); }
		</SCRIPT></body>
		</html>
    ';
		echo $submit_form;
		// Mage::getSingleton('core/session')->unsAcsurl();
		// Mage::getSingleton('core/session')->unsPa();
		// Mage::getSingleton('core/session')->unsTemurl();
		// Mage::getSingleton('core/session')->unsMD();
		// Mage::getSingleton('core/session')->unsStatus();
		//Mage::log('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaabbbbbbbbbbbbbbbbb');
    }
}
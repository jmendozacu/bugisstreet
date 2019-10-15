<?php


class WACI_CustomerExt_Model_Observer
{

    public function __construct()
    {

    }

    public function customer_register_success($observer)
    {
        $emailTemplate  = Mage::getModel('core/email_template')
            ->loadDefault('notify_new_customer');

        $emailreceive = Mage::getStoreConfig('trans_email/ident_general/email');
        $namereceive = Mage::getStoreConfig('trans_email/ident_general/name');
        $customer = $observer->getCustomer()->getData();
        $emailTemplateVariables = array();
        $emailTemplateVariables["myvar1"] = $customer['email'];
        $emailTemplateVariables["myvar2"] = $customer['firstname'];
        $emailTemplateVariables["myvar3"] = $customer['lastname'];
        $emailTemplateVariables["myvar7"] = "";
        $emailTemplateVariables["myvar4"] = "";
        $emailTemplateVariables["myvar5"] = "";
        $emailTemplateVariables["myvar6"] = "";
        if($customer['contactno']!=''){
            $emailTemplateVariables["myvar7"] = "Want to be seller: ";
            $emailTemplateVariables["myvar4"] = "Contact No: ".$customer['contactno'];
            $emailTemplateVariables["myvar5"] = "Shop Name: ".$customer['shopname'];
            $emailTemplateVariables["myvar6"] = "Shop Description: ".$customer['shopdescription'];
        }
        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
        $mail = Mage::getModel("core/email")
            ->setToName($namereceive)
            ->setToEmail($emailreceive)
            ->setBody($processedTemplate)
            ->setSubject("Information new customer")
            ->setType("html");
            try{
                $mail->send();
            }
            catch(Exception $error)
            {
                Mage::getSingleton("core/session")->addError($error->getMessage());
                return false;
            }
    }

}
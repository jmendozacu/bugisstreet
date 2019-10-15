<?php

class Inchoo_Order_Model_Observer
{
    public function cancelPendingOrders()
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');

        $orderCollection
            ->addFieldToFilter('state', 'complete')
            ->addFieldToFilter('is_reminded', 0)
            ->addFieldToFilter('updated_at', array(
                'lt' =>  new Zend_Db_Expr("DATE_ADD('".now()."', INTERVAL -1 WEEK)")))
            ->getSelect()
            ->order('entity_id')
            ->limit(50)
        ;

        foreach($orderCollection->getItems() as $order)
        {
            $orderModel = Mage::getModel('sales/order');
            $orderModel->load($order['entity_id']);

            /*if(!$orderModel->canCancel())
                continue;

            $orderModel->cancel();
            $orderModel->setStatus('canceled');
            $orderModel->save();*/


            // Sent mail
            $enable = Mage::getStoreConfig('cancel_order_notification/cancel_order_email_notification/cancel_order_enable');
            if($enable){

                $mailSubject = 'Order #' . $orderModel->increment_id . 'send after 3 days for customer do review';

                // Transactional Email Template's ID
                //$templateId = 'cancel_order_email_template';
                $templateId = Mage::getStoreConfig('cancel_order_notification/cancel_order_email_notification/cancel_order_email');

                // Set sender information
                $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
                $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');
                $sender = array('name' => $senderName, 'email' => $senderEmail);

                // Set recepient information
                $recepientEmail = $orderModel->getCustomerEmail();
                $recepientName = $orderModel->getCustomerName();

                // Set variables that can be used in email template
                $vars = array(
                    'orderModel' => $orderModel,
                    'orderUrl' => '',
                    'customerUrl' => '',
                );
                // Send Transactional Email
                Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)
                    ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars);
                $orderModel->setIsReminded(1);
                $orderModel->save();
            }

        }


    }

}
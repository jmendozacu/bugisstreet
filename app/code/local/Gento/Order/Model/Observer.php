<?php

class Gento_Order_Model_Observer
{
    public function cancelPendingOrders()
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');

        $orderCollection
            ->addFieldToFilter('status', 'pending')
            ->addFieldToFilter('created_at', array(
                'lt' =>  new Zend_Db_Expr("DATE_ADD('".now()."', INTERVAL -'25:00' HOUR_MINUTE)")))
            ->getSelect()
            ->order('entity_id');
            //->limit(50)

        //$orders ="";
        foreach($orderCollection->getItems() as $order)
        {
            $orderModel = Mage::getModel('sales/order');
            $orderModel->load($order['entity_id']);

            if(!$orderModel->canCancel())
                continue;

            $orderModel->cancel();
            $orderModel->setStatus('canceled');
            $orderModel->save();

        }

    }

}
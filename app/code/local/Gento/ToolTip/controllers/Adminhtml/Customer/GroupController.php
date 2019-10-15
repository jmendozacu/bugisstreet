<?php
require_once 'Mage/Adminhtml/controllers/Customer/GroupController.php';
class Gento_ToolTip_Adminhtml_Customer_GroupController extends Mage_Adminhtml_Customer_GroupController {
    /**
     * Create or save customer group.
     */
    public function saveAction()
    {
        $customerGroup = Mage::getModel('customer/group');
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            $customerGroup->load((int)$id);
        }
        $taxClass = (int)$this->getRequest()->getParam('tax_class');
        $group = (int)$this->getRequest()->getParam('member_type');
        $pl = (int)$this->getRequest()->getParam('product_listing');
        $tp = (int)$this->getRequest()->getParam('transaction_percent');
        if ($taxClass) {
            try {
                $customerGroupCode = (string)$this->getRequest()->getParam('code');

                if (!empty($customerGroupCode)) {
                    $customerGroup->setCode($customerGroupCode);
                }

                $customerGroup->setTaxClassId($taxClass)->save();
                $customerGroup->setMemberType($group)->save();
                $customerGroup->setProductListing($pl)->save();
                $customerGroup->setTransactionPercent($tp)->save();
                if (!is_null($id)) {
                    if($group==2){
                        $collection = Mage::getModel('marketplace/userprofile')->getCollection()
                            ->addFieldToFilter("member", $id);
                        foreach($collection as $row){
                            $userid = $row['mageuserid'];
                            $resource = Mage::getSingleton('core/resource');
                            $writeConnection = $resource->getConnection('core_write');
                            $table = 'marketplace_saleperpartner';
                            $query = 'UPDATE ' . $table . ' SET commision = ' . $tp .' WHERE mageuserid = ' . $userid;
                            $writeConnection->query($query);
                        }
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customer')->__('The customer group has been saved.'));
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCustomerGroupData($customerGroup->getData());
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group/edit', array('id' => $id)));
                return;
            }
        } else {
            $this->_forward('new');
        }
    }
    public function deleteAction()
    {
        $customerGroup = Mage::getModel('customer/group');
        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $customerGroup->load($id);

                $groups = Mage::getResourceModel('customer/group_collection')
                    ->addFieldToFilter('customer_group_id', $id)
                    ->load();
                foreach($groups as $group){
                    $limit = $group['member_type'];
                }

                $groups2 = Mage::getResourceModel('customer/group_collection')
                    ->addFieldToFilter('customer_group_id', '4')
                    ->load();
                foreach($groups2 as $group2){
                    $limit2 = $group2['transaction_percent'];
                }
                if($limit==2){
                    $collection = Mage::getModel('marketplace/userprofile')->getCollection()
                        ->addFieldToFilter("member", $id);
                    foreach($collection as $row){
                        $userid = $row['mageuserid'];

                        $collection1 = Mage::getModel('marketplace/userprofile')->getCollection();
                        $collection1->addFieldToFilter('mageuserid',array('eq'=>$userid));
                        foreach($collection1 as $value){
                            $value->setmember("4");
                            $value->save();
                        }
                       $resource = Mage::getSingleton('core/resource');
                        $writeConnection = $resource->getConnection('core_write');
                        $table = 'marketplace_saleperpartner';
                        $query = 'UPDATE ' . $table . ' SET commision = ' . $limit2 .' WHERE mageuserid = ' . $userid;
                       $writeConnection->query($query);
                    }
                }

                $customerGroup->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customer')->__('The customer group has been deleted.'));
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group/edit', array('id' => $id)));
                return;
            }
        }

        $this->_redirect('*/customer_group');
    }

}
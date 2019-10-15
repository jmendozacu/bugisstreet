<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @Author     Ocean <ocean@gentotech.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ocean_CustomerAttributes_Helper_Customer extends Ocean_CustomerAttributes_Helper_Eav
{
    /**
     * Default attribute entity type code
     *
     * @return string
     */
    protected function _getEntityTypeCode()
    {
        return 'customer';
    }

    /**
     * Return available customer attribute form as select options
     *
     * @return array
     */
    public function getAttributeFormOptions()
    {
        return array(
            array(
                'label' => Mage::helper('customer_attributes')->__('Customer Checkout Register'),
                'value' => 'checkout_register'
            ),
            array(
                'label' => Mage::helper('customer_attributes')->__('Customer Registration'),
                'value' => 'customer_account_create'
            ),
            array(
                'label' => Mage::helper('customer_attributes')->__('Customer Account Edit'),
                'value' => 'customer_account_edit'
            ),
            array(
                'label' => Mage::helper('customer_attributes')->__('Admin Checkout'),
                'value' => 'adminhtml_checkout'
            ),
        );
    }
}
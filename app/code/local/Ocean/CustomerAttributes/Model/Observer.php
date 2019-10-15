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
class Ocean_CustomerAttributes_Model_Observer
{
    /**
     * Before save observer for customer attribute
     *
     * @param Varien_Event_Observer $observer
     * @return Ocean_CustomerAttributes_Model_Observer
     */
    public function customerAttributeBeforeSave(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof Mage_Customer_Model_Attribute && $attribute->isObjectNew()) {
            /**
             * Check for maximum attribute_code length
             */
            $attributeCodeMaxLength = Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH - 9;
            $validate = Zend_Validate::is($attribute->getAttributeCode(), 'StringLength', array(
                'max' => $attributeCodeMaxLength
            ));
            if (!$validate) {
                throw Mage::exception('Mage_Eav',
                    Mage::helper('eav')->__('Maximum length of attribute code must be less then %s symbols', $attributeCodeMaxLength)
                );
            }
        }

        return $this;
    }

    /**
     * After save observer for customer attribute
     *
     * @param Varien_Event_Observer $observer
     * @return Ocean_CustomerAttributes_Model_Observer
     */
    public function customerAttributeSave(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof Mage_Customer_Model_Attribute && $attribute->isObjectNew()) {
            Mage::getModel('customer_attributes/sales_quote')
                ->saveNewAttribute($attribute);
            Mage::getModel('customer_attributes/sales_order')
                ->saveNewAttribute($attribute);
        }

        return $this;
    }

    /**
     * After delete observer for customer attribute
     *
     * @param Varien_Event_Observer $observer
     * @return Ocean_CustomerAttributes_Model_Observer
     */
    public function customerAttributeDelete(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof Mage_Customer_Model_Attribute && !$attribute->isObjectNew()) {
            Mage::getModel('customer_attributes/sales_quote')
                ->deleteAttribute($attribute);
            Mage::getModel('customer_attributes/sales_order')
                ->deleteAttribute($attribute);
        }

        return $this;
    }
}
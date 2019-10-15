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
class Ocean_CustomerAttributes_Block_Adminhtml_Customer_Attribute_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Return current customer address attribute instance
     *
     * @return Mage_Customer_Model_Attribute
     */
    protected function _getAttribute()
    {
        return Mage::registry('entity_attribute');
    }

    /**
     * Initialize Customer Address Attribute Edit Container
     *
     */
    public function __construct()
    {
        $this->_objectId    = 'attribute_id';
        $this->_blockGroup  = 'customer_attributes';
        $this->_controller  = 'adminhtml_customer_attribute';

        parent::__construct();

        $this->_addButton(
            'save_and_edit_button',
            array(
                'label'     => Mage::helper('customer_attributes')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save'
            ),
            100
        );

        $this->_updateButton('save', 'label', Mage::helper('customer_attributes')->__('Save Attribute'));

        if (!$this->_getAttribute()->getIsUserDefined()) {
            $this->_removeButton('delete');
        } else {
            $this->_updateButton('delete', 'label', Mage::helper('customer_attributes')->__('Delete Attribute'));
        }
    }

    /**
     * Return header text for edit block
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_getAttribute()->getId()) {
            $label = $this->_getAttribute()->getFrontendLabel();
            if (is_array($label)) {
                // restored label
                $label = $label[0];
            }
            return Mage::helper('customer_attributes')->__('Edit Customer Attribute "%s"', $label);
        } else {
            return Mage::helper('customer_attributes')->__('New Customer Attribute');
        }
    }

    /**
     * Return validation url for edit form
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current' => true));
    }

    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'back' => null));
    }
}
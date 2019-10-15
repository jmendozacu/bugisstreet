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
class Ocean_CustomerAttributes_Block_Adminhtml_Customer_Attribute extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Define controller, block and labels
     *
     */
    public function __construct()
    {
        $this->_blockGroup = 'customer_attributes';
        $this->_controller = 'adminhtml_customer_attribute';
        $this->_headerText = Mage::helper('customer_attributes')->__('Manage Customer Attributes');
        $this->_addButtonLabel = Mage::helper('customer_attributes')->__('Add New Attribute');
        parent::__construct();
    }
}
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
class Ocean_CustomerAttributes_Block_Adminhtml_Customer_Attribute_Grid
    extends Mage_Eav_Block_Adminhtml_Attribute_Grid_Abstract
{
    /**
     * Initialize grid, set grid Id
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('customerAttributeGrid');
        $this->setDefaultSort('sort_order');
    }

    /**
     * Prepare customer attributes grid collection object
     *
     * @return Enterprise_Customer_Block_Adminhtml_Customer_Attribute_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/attribute_collection')
            ->addSystemHiddenFilter()
            ->addExcludeHiddenFrontendFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare customer attributes grid columns
     *
     * @return Enterprise_Customer_Block_Adminhtml_Customer_Attribute_Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('is_visible', array(
            'header'    => Mage::helper('customer_attributes')->__('Visible on Frontend'),
            'sortable'  => true,
            'index'     => 'is_visible',
            'type'      => 'options',
            'options'   => array(
                '0' => Mage::helper('customer_attributes')->__('No'),
                '1' => Mage::helper('customer_attributes')->__('Yes'),
            ),
            'align'     => 'center',
        ));

        $this->addColumn('sort_order', array(
            'header'    => Mage::helper('customer_attributes')->__('Sort Order'),
            'sortable'  => true,
            'align'     => 'center',
            'index'     => 'sort_order'
        ));

        return $this;
    }
}
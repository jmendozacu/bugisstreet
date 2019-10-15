<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Gt
 * @package     Gt_Gtbanner
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * General form
 *
 * @category   Gt
 * @package    Gt_Gtbanner
 * @author     Gentotech <http://www.gentotech.com/>
 */

class Gt_Gtbanner_Block_Adminhtml_Banner_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_model = Mage::registry('banner_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('gtbanner_form', array('legend'=>Mage::helper('gtbanner')->__('General Information')));
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('gtbanner')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
            'value'     => $_model->getName()
        ));

        $fieldset->addField('position', 'select', array(
            'label'     => Mage::helper('gtbanner')->__('Position'),
            'name'      => 'position',
            'values'    => Mage::getSingleton('gtbanner/config_source_position')->toOptionArray(),
            'value'     => $_model->getPosition()
        ));

        $fieldset->addField('sort_order', 'text', array(
            'label'     => Mage::helper('gtbanner')->__('Sort Order'),
            'required'  => false,
            'name'      => 'sort_order',
            'value'     => $_model->getSortOrder()
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('gtbanner')->__('Is Active'),
            'name'      => 'is_active',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'value'     => $_model->getIsActive()
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('stores', 'multiselect', array(
                'label'     => Mage::helper('gtbanner')->__('Visible In'),
                'required'  => true,
                'name'      => 'stores[]',
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
                'value'     => $_model->getStoreId()
            ));
        }
        else {
            $fieldset->addField('stores', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
        }


        if( Mage::getSingleton('adminhtml/session')->getBannerData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBannerData());
            Mage::getSingleton('adminhtml/session')->setBannerData(null);
        }
        
        return parent::_prepareForm();
    }
}

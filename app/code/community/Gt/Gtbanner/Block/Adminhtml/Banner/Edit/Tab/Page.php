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
 * Page block
 *
 * @category   Gt
 * @package    Gt_Gtbanner
 * @author     Gentotech <http://www.gentotech.com/>
 */

class Gt_Gtbanner_Block_Adminhtml_Banner_Edit_Tab_Page extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_model = Mage::registry('banner_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('gtbanner_form', array('legend'=>Mage::helper('gtbanner')->__('Banner Pages')));
        $fieldset->addField('pages', 'multiselect', array(
            'label'     => Mage::helper('gtbanner')->__('Visible In'),
            'required'  => true,
            'name'      => 'pages[]',
            'values'    => Mage::getSingleton('gtbanner/config_source_page')->toOptionArray(),
            'value'     => $_model->getPageId()
        ));
        
        return parent::_prepareForm();
    }
}

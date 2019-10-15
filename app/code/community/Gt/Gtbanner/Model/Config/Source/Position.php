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
 * Position config model
 *
 * @category   Gt
 * @package    Gt_Gtbanner
 * @author     Gentotech <http://www.gentotech.com/>
 */
class Gt_Gtbanner_Model_Config_Source_Position
{
    const CONTENT_TOP       = 'CONTENT_TOP';
    const CONTENT_BOTTOM    = 'CONTENT_BOTTOM';
	const CONTENT_BRANDS    = 'CONTENT_BRANDS';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::CONTENT_TOP, 'label'=>Mage::helper('adminhtml')->__('Content Top')),
            array('value' => self::CONTENT_BOTTOM, 'label'=>Mage::helper('adminhtml')->__('Content Bottom')),
			array('value' => self::CONTENT_BRANDS, 'label'=>Mage::helper('adminhtml')->__('Content Brands'))
        );
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toGridOptionArray()
    {
        return array(
            self::CONTENT_TOP => Mage::helper('adminhtml')->__('Content Top'),
            self::CONTENT_BOTTOM => Mage::helper('adminhtml')->__('Content Bottom'),
			self::CONTENT_BRANDS => Mage::helper('adminhtml')->__('Content Brands')
        );
    }
}

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
 * Layout generat observer
 *
 * @category   Gt
 * @package    Gt_Gtbanner
 * @author     Gentotech <http://www.gentotech.com/>
 */

class Gt_Gtbanner_Model_Layout_Generate_Observer {
    const XML_PATH_ENABLE_JQUERY = 'gtbanner/general/enable_jquery';

    /**
     * Add Jquery library depends on configuration value
     * @return int $count
     */
	public function addJqueryLibrary($observer) {
        $enableJquery = Mage::getStoreConfig(self::XML_PATH_ENABLE_JQUERY);
        if ($enableJquery == 1) {
            $_head = $this->__getHeadBlock();
            if ($_head) {
                //$_head->addFirst('js', 'gt_gtbanner/jquery.js');
                //$_head->addAfter('js', 'gt_gtbanner/jquery.noconflict.js', 'gt_gtbanner/jquery.js');
            }
        }
	}

    /*
     * Get head block
     */
    private function __getHeadBlock() {
        return Mage::getSingleton('core/layout')->getBlock('gt_gtbanner_head');
    }
}
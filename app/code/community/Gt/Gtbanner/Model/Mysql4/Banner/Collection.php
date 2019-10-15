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
 * Banner Resource Collection
 *
 * @category   Gt
 * @package    Gt_Gtbanner
 * @author     Gentotech <http://www.gentotech.com/>
 */
class Gt_Gtbanner_Model_Mysql4_Banner_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    /**
     * Constructor method
     */
    protected function _construct() {
        $this->_init('gtbanner/banner');
    }

    /**
     * Add Filter by position
     *
     * @param string $position
     * @return Gt_Gtbanner_Model_Mysql4_Banner_Collection
     */
    public function addPositionFilter($position) {
        $this->getSelect()->where('main_table.position = ?', $position);
        return $this;
    }

    /**
     * Add Filter by category
     *
     * @param int $category
     * @return Gt_Gtbanner_Model_Mysql4_Banner_Collection
     */
    public function addCategoryFilter($category) {
        $this->getSelect()->join(
                array('category_table' => $this->getTable('gtbanner/banner_category')),
                'main_table.banner_id = category_table.banner_id',
                array()
                )
                ->where('category_table.category_id = ?', $category);
        return $this;
    }

    /**
     * Add Filter by page
     *
     * @param int $page
     * @return Gt_Gtbanner_Model_Mysql4_Banner_Collection
     */
    public function addPageFilter($page) {
        $this->getSelect()->join(
                array('page_table' => $this->getTable('gtbanner/banner_page')),
                'main_table.banner_id = page_table.banner_id',
                array()
                )
                ->where('page_table.page_id = ?', $page);
        return $this;
    }

    /**
     * Add Filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @return Gt_Gtbanner_Model_Mysql4_Banner_Collection
     */
    public function addStoreFilter($store) {
        if (!Mage::app()->isSingleStoreMode()) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = array($store->getId());
            }

            $this->getSelect()->join(
                    array('store_table' => $this->getTable('gtbanner/banner_store')),
                    'main_table.banner_id = store_table.banner_id',
                    array()
                    )
                    ->where('store_table.store_id in (?)', array(0, $store));
            return $this;
        }
        return $this;
    }

    /**
     * Add Filter by status
     *
     * @param int $status
     * @return Gt_Gtbanner_Model_Mysql4_Banner_Collection
     */
    public function addEnableFilter($status = 1) {
        $this->getSelect()->where('main_table.is_active = ?', $status);
        return $this;
    }
}
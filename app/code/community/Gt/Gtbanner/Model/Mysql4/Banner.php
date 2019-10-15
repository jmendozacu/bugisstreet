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
 * Banner Resource Model
 *
 * @category   Gt
 * @package    Gt_Gtbanner
 * @author     Gentotech <http://www.gentotech.com/>
 */
class Gt_Gtbanner_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Initialize resource model
     */
    protected function _construct() {
        $this->_init('gtbanner/banner', 'banner_id');
    }

    /**
     * Load images
     */
    public function loadImage(Mage_Core_Model_Abstract $object) {
        return $this->__loadImage($object);
    }

    /**
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        if (!$object->getIsMassDelete()) {
            $object = $this->__loadStore($object);
            $object = $this->__loadPage($object);
            $object = $this->__loadCategory($object);
            $object = $this->__loadImage($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object) {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($data = $object->getStoreId()) {
            $select->join(
                    array('store' => $this->getTable('gtbanner/banner_store')), $this->getMainTable().'.banner_id = `store`.banner_id')
                    ->where('`store`.store_id in (0, ?) ', $data);
        }
        if ($data = $object->getPageId()) {
            $select->join(
                    array('page' => $this->getTable('gtbanner/banner_page')), $this->getMainTable().'.banner_id = `page`.banner_id')
                    ->where('`page`.page_id in (?) ', $data);
        }
        if ($data = $object->getCategoryId()) {
            $select->join(
                    array('category' => $this->getTable('gtbanner/banner_category')), $this->getMainTable().'.banner_id = `category`.banner_id')
                    ->where('`category`.category_id in (?) ', $data);
        }
        $select->order('name DESC')->limit(1);

        return $select;
    }

    /**
     * Call-back function
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        if (!$object->getIsMassStatus()) {
            $this->__saveToStoreTable($object);
            $this->__saveToPageTable($object);
            $this->__saveToCategoryTable($object);
            $this->__saveToImageTable($object);
        }

        return parent::_afterSave($object);
    }

    /**
     * Call-back function
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object) {
        // Cleanup stats on blog delete
        $adapter = $this->_getReadAdapter();
        // 1. Delete blog/store
        $adapter->delete($this->getTable('gtbanner/banner_store'), 'banner_id='.$object->getId());
        // 2. Delete blog/post_cat
        $adapter->delete($this->getTable('gtbanner/banner_page'), 'banner_id='.$object->getId());
        // 3. Delete blog/post_comment
        $adapter->delete($this->getTable('gtbanner/banner_category'), 'banner_id='.$object->getId());
        // Update tags

        return parent::_beforeDelete($object);
    }

    /**
     * Load stores
     */
    private function __loadStore(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('gtbanner/banner_store'))
                ->where('banner_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['store_id'];
            }
            $object->setData('store_id', $array);
        }
        return $object;
    }

    /**
     * Load pages
     */
    private function __loadPage(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('gtbanner/banner_page'))
                ->where('banner_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['page_id'];
            }
            $object->setData('page_id', $array);
        }
        return $object;
    }

    /**
     * Load categories
     */
    private function __loadCategory(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('gtbanner/banner_category'))
                ->where('banner_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['category_id'];
            }
            $object->setData('category_id', $array);
        }
        return $object;
    }

    /**
     * Load images
     */
    private function __loadImage(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('gtbanner/banner_image'))
                ->where('banner_id = ?', $object->getId())
                ->where('disabled = ?', 0)
                ->order(array('position ASC', 'image_id'));

        $object->setData('image', $this->_getReadAdapter()->fetchAll($select));
        return $object;
    }

    /**
     * Save stores
     */
    private function __saveToStoreTable(Mage_Core_Model_Abstract $object) {
        if (!$object->getData('stores')) {
            $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
            $this->_getWriteAdapter()->delete($this->getTable('gtbanner/banner_store'), $condition);

            $storeArray = array(
                'banner_id' => $object->getId(),
                'store_id' => '0');
            $this->_getWriteAdapter()->insert(
                    $this->getTable('gtbanner/banner_store'), $storeArray);
            return true;
        }

        /*$object = $this->__loadStore($object);
        if ($storeList = $object->getStoreId()) {
            $first = implode('|',asort($storeList));
            $second = implode('|',asort($object->getData('stores')));
            if (strcmp($first,$second) == 0) {
                return true;
            }
        }*/

        $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('gtbanner/banner_store'), $condition);
        foreach ((array)$object->getData('stores') as $store) {
            $storeArray = array();
            $storeArray['banner_id'] = $object->getId();
            $storeArray['store_id'] = $store;
            $this->_getWriteAdapter()->insert(
                    $this->getTable('gtbanner/banner_store'), $storeArray);
        }
    }

    /**
     * Save stores
     */
    private function __saveToPageTable(Mage_Core_Model_Abstract $object) {
        if ($data = $object->getData('pages')) {
            /*$object = $this->__loadPage($object);
            if ($IDList = $object->getPageId()) {
                $first = implode('|',asort($IDList));
                $second = implode('|',asort($data));
                if (strcmp($first,$second) == 0) {
                    return true;
                }
            }*/

            $this->_getWriteAdapter()->beginTransaction();
            try {
                $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
                $this->_getWriteAdapter()->delete($this->getTable('gtbanner/banner_page'), $condition);

                foreach ((array)$data as $page) {
                    $pageArray = array();
                    $pageArray['banner_id'] = $object->getId();
                    $pageArray['page_id'] = $page;
                    $this->_getWriteAdapter()->insert(
                            $this->getTable('gtbanner/banner_page'), $pageArray);
                }
                $this->_getWriteAdapter()->commit();
            } catch (Exception $e) {
                $this->_getWriteAdapter()->rollBack();
                echo $e->getMessage();
            }
            return true;
        }

        $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('gtbanner/banner_page'), $condition);
    }

    /**
     * Save categories
     */
    private function __saveToCategoryTable(Mage_Core_Model_Abstract $object) {
        if ($data = $object->getData('categories')) {
            /*$object = $this->__loadCategory($object);
            if ($IDList = $object->getCategoryId()) {
                $first = implode('|',asort($IDList));
                $second = implode('|',asort($data));
                if (strcmp($first,$second) == 0) {
                    return true;
                }
            }*/

            $this->_getWriteAdapter()->beginTransaction();
            try {
                $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
                $this->_getWriteAdapter()->delete($this->getTable('gtbanner/banner_category'), $condition);

                $data = array_unique($data);
                foreach ((array)$data as $category) {
                    $categoryArray = array();
                    $categoryArray['banner_id'] = $object->getId();
                    $categoryArray['category_id'] = $category;
                    $this->_getWriteAdapter()->insert(
                            $this->getTable('gtbanner/banner_category'), $categoryArray);
                }
                $this->_getWriteAdapter()->commit();
            } catch (Exception $e) {
                $this->_getWriteAdapter()->rollBack();
                echo $e->getMessage();
            }
            return true;
        }

        $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('gtbanner/banner_category'), $condition);
    }

    /**
     * Save stores
     */
    private function __saveToImageTable(Mage_Core_Model_Abstract $object) {
        if ($_imageList = $object->getData('images')) {
            $_imageList = Zend_Json::decode($_imageList);
            if (is_array($_imageList) and sizeof($_imageList) > 0) {
                $_imageTable = $this->getTable('gtbanner/banner_image');
                $_adapter = $this->_getWriteAdapter();
                $_adapter->beginTransaction();
                try {
                    $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
                    $this->_getWriteAdapter()->delete($this->getTable('gtbanner/banner_image'), $condition);

                    foreach ($_imageList as &$_item) {
                        if (isset($_item['removed']) and $_item['removed'] == '1') {
                            $_adapter->delete($_imageTable, $_adapter->quoteInto('image_id = ?', $_item['value_id'], 'INTEGER'));
                        } else {
                            $_data = array(
                                'label'     => $_item['label'],
                                'file'      => $_item['file'],
                                'position'  => $_item['position'],
                                'disabled'  => $_item['disabled'],
                                'banner_id' => $object->getId());
                            $_adapter->insert($_imageTable, $_data);
                        }
                    }
                    $_adapter->commit();
                } catch (Exception $e) {
                    $_adapter->rollBack();
                    echo $e->getMessage();
                }
            }
        }
    }
}
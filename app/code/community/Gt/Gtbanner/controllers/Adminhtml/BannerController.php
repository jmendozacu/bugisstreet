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
 * Banner controller
 *
 * @category   Gt
 * @package    Gt_Gtbanner
 * @author     Gentotech <http://www.gentotech.com/>
 */
class Gt_Gtbanner_Adminhtml_BannerController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('gtbanner');

        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('Gt-Banner Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('gtbanner/adminhtml_banner'));
        $this->renderLayout();
    }

    public function addAction() {
        $this->_title($this->__('New Banner'));

        $_model  = Mage::getModel('gtbanner/banner');
        Mage::register('banner_data', $_model);
        Mage::register('current_banner', $_model);

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Gt-Banner Manager'), Mage::helper('adminhtml')->__('Gt-Banner Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Banner'), Mage::helper('adminhtml')->__('Add Banner'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('gtbanner/adminhtml_banner_add'))
                ->_addLeft($this->getLayout()->createBlock('gtbanner/adminhtml_banner_add_tabs'));

        $this->renderLayout();
    }

    public function editAction() {
        $bannerId     = $this->getRequest()->getParam('id');
        $_model  = Mage::getModel('gtbanner/banner')->load($bannerId);

        if ($_model->getId()) {
            $this->_title($_model->getId() ? $_model->getName() : $this->__('New Banner'));

            Mage::register('banner_data', $_model);
            Mage::register('current_banner', $_model);

            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Gt-Banner Manager'), Mage::helper('adminhtml')->__('Gt-Banner Manager'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Banner'), Mage::helper('adminhtml')->__('Edit Banner'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('gtbanner/adminhtml_banner_edit'))
                    ->_addLeft($this->getLayout()->createBlock('gtbanner/adminhtml_banner_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gtbanner')->__('The banner does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $_model = Mage::getModel('gtbanner/banner');
            if (isset($data['categories'])) {
                $data['categories'] = explode(',',$data['categories']);
                if (is_array($data['categories'])) {
                    $data['categories'] = array_unique($data['categories']);
                }
            }

            $_model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                $_model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('gtbanner')->__('Banner was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $_model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gtbanner')->__('Unable to find banner to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('gtbanner/banner');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Banner was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $IDList = $this->getRequest()->getParam('banner');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select banner(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getModel('gtbanner/banner')
                            ->setIsMassDelete(true)->load($itemId);
                    $_model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($IDList)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $IDList = $this->getRequest()->getParam('banner');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select banner(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getSingleton('gtbanner/banner')
                            ->setIsMassStatus(true)
                            ->load($itemId)
                            ->setIsActive($this->getRequest()->getParam('status'))
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($IDList))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function imageAction() {
        $result = array();
        try {
            $uploader = new Gt_Gtbanner_Media_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                    Mage::getSingleton('gtbanner/config')->getBaseMediaPath()
            );
			
			/**
             * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
             */
            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);

            $result['url'] = Mage::getSingleton('gtbanner/config')->getMediaUrl($result['file']);
            $result['cookie'] = array(
                    'name'     => session_name(),
                    'value'    => $this->_getSession()->getSessionId(),
                    'lifetime' => $this->_getSession()->getCookieLifetime(),
                    'path'     => $this->_getSession()->getCookiePath(),
                    'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function categoriesJsonAction()
    {
        $bannerId     = $this->getRequest()->getParam('id');
        $_model  = Mage::getModel('gtbanner/banner')->load($bannerId);
        Mage::register('banner_data', $_model);
        Mage::register('current_banner', $_model);

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('gtbanner/adminhtml_banner_edit_tab_category')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    /**
     * Add an extra title to the end or one from the end, or remove all
     *
     * Usage examples:
     * $this->_title('foo')->_title('bar');
     * => bar / foo / <default title>
     *
     * $this->_title()->_title('foo')->_title('bar');
     * => bar / foo
     *
     * $this->_title('foo')->_title(false)->_title('bar');
     * bar / <default title>
     *
     * @see self::_renderTitles()
     * @param string|false|-1|null $text
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _title($text = null, $resetIfExists = true)
    {
        if (is_string($text)) {
            $this->_titles[] = $text;
        } elseif (-1 === $text) {
            if (empty($this->_titles)) {
                $this->_removeDefaultTitle = true;
            } else {
                array_pop($this->_titles);
            }
        } elseif (empty($this->_titles) || $resetIfExists) {
            if (false === $text) {
                $this->_removeDefaultTitle = false;
                $this->_titles = array();
            } elseif (null === $text) {
                $this->_removeDefaultTitle = true;
                $this->_titles = array();
            }
        }
        return $this;
    }
}
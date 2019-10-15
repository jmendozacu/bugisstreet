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
/**
 * Customer abstract model
 *
 */
abstract class Ocean_CustomerAttributes_Model_Sales_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Save new attribute
     *
     * @param Mage_Customer_Model_Attribute $attribute
     * @return Ocean_CustomerAttributes_Model_Sales_Abstract
     */
    public function saveNewAttribute(Mage_Customer_Model_Attribute $attribute)
    {
        $this->_getResource()->saveNewAttribute($attribute);
        return $this;
    }

    /**
     * Delete attribute
     *
     * @param Mage_Customer_Model_Attribute $attribute
     * @return Ocean_CustomerAttributes_Model_Sales_Abstract
     */
    public function deleteAttribute(Mage_Customer_Model_Attribute $attribute)
    {
        $this->_getResource()->deleteAttribute($attribute);
        return $this;
    }

    /**
     * Attach extended data to sales object
     *
     * @param Mage_Core_Model_Abstract $sales
     * @return Ocean_CustomerAttributes_Model_Sales_Abstract
     */
    public function attachAttributeData(Mage_Core_Model_Abstract $sales)
    {
        $sales->addData($this->getData());
        return $this;
    }

    /**
     * Save extended attributes data
     *
     * @param Mage_Core_Model_Abstract $sales
     * @return Ocean_CustomerAttributes_Model_Sales_Abstract
     */
    public function saveAttributeData(Mage_Core_Model_Abstract $sales)
    {
        $this->addData($sales->getData())
            ->setId($sales->getId())
            ->save();

        return $this;
    }

    /**
     * Processing object before save data.
     * Need to check if main entity is already deleted from the database:
     * we should not save additional attributes for deleted entities.
     *
     * @return Ocean_CustomerAttributes_Model_Sales_Abstract
     */
    protected function _beforeSave()
    {
        if ($this->_dataSaveAllowed && !$this->_getResource()->isEntityExists($this)) {
            $this->_dataSaveAllowed = false;
        }
        return parent::_beforeSave();
    }
}
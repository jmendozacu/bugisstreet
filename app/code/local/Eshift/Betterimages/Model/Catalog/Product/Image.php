<?php

/**
 * Better Product Images
 *
 * @copyright   Copyright (c) 2014 eCommerce Shift. (http://www.ecommerceshift.com)
 */

class Eshift_Betterimages_Model_Catalog_Product_Image extends Mage_Catalog_Model_Product_Image
{
    /**
     * @see Varien_Image_Adapter_Abstract
     * @return Mage_Catalog_Model_Product_Image
     */
    public function resize()
    {
        if (is_null($this->getWidth()) && is_null($this->getHeight())) {
            return $this;
        }
        $this->getImageProcessor()->resize($this->_width, $this->_height);

    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */

    public function sharpen($value) {
        if (is_null($value)) {
            return $this;
        }

        $this->getImageProcessor()->sharpen($value);

        return $this;
    }
}

<?php

/**
 * Better Product Images
 *
 * @copyright   Copyright (c) 2014 eCommerce Shift. (http://www.ecommerceshift.com)
 */

/**
 * Catalog image helper
 *
 */

class Eshift_Betterimages_Helper_Catalog_Image extends Mage_Catalog_Helper_Image
{
    /*
     * Sharpen Value
     */
    protected $_sharpenValue = null;

    /*
     * Shedule Sharpen
     */
    protected $_sheduleSharpen = false;

    /**
     * Return Image URL
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $model = $this->_getModel();

            if ($this->getImageFile()) {
                $model->setBaseFile($this->getImageFile());
            } else {
                $model->setBaseFile($this->getProduct()->getData($model->getDestinationSubdir()));
            }

            if ($model->isCached()) {
                return $model->getUrl();
            } else {

                $this->prepareImageSettings();

                if ($this->_scheduleRotate) {
                    $model->rotate($this->getAngle());
                }

                if ($this->_scheduleResize) {
                    $model->resize();
                }

                if ($this->getWatermark()) {
                    $model->setWatermark($this->getWatermark());
                }

                if ($this->_sheduleSharpen) {
                    $model->sharpen($this->_sharpenValue);
                }

                $url = $model->saveFile()->getUrl();
            }
        } catch (Exception $e) {
            $url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
        }
        return $url;
    }

    public function prepareImageSettings(){

        /*
         * Prepare config values
         */

        $model = $this->_getModel();

        $_defaultImages = array('image','small_image','thumbnail');

        $_ImageType = $model->getDestinationSubdir();

        if (!in_array($_ImageType,$_defaultImages)) {
            $_ImageType = 'other';
        }

        /*
         * Get and set sharpen value if needed
         */

        $globalSharpen = Mage::getStoreConfig("es_betterimages/{$_ImageType}/enable_sharpen");
        $globalSharpenValue = floatval(Mage::getStoreConfig("es_betterimages/{$_ImageType}/sharpen_value"));

        /*
        * Get and set sharpen value if needed
        */

        if ($globalSharpen && $globalSharpenValue) {
            $this->_sharpenValue = $globalSharpenValue;
        }

        /*
        * Sharpen also may be set with setSharpen($value) directly when calling catalog/image helper
        */

        if ($this->_sharpenValue) {
            $this->_sheduleSharpen = true;
        }


        /*
         * Set quality for image from admin
         */

        $globalQuality = Mage::getStoreConfig("es_betterimages/{$_ImageType}/quality");

        if($globalQuality) {
            $this->setQuality($globalQuality);
        }

        return $this;
    }
}

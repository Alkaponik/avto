<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Articles
 *
 * @author Oleg
 */
class Testimonial_MageDoc_Model_Tecdoc_Manufacturer extends Testimonial_MageDoc_Model_Abstract
{
    protected static $_manufacturers = array();

    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_manufacturer');
    }

    public function factory($manufacturerId)
    {
        if (!isset(self::$_manufacturers[$manufacturerId])){
            self::$_manufacturers[$manufacturerId] = new self();
            self::$_manufacturers[$manufacturerId]
                ->isPartialLoad(false)
                ->load($manufacturerId);
        }
        return self::$_manufacturers[$manufacturerId];
    }

    public function getUrl($useSid = null)
    {
        return $this->getUrlModel()->getManufacturerUrl($this, $useSid);
    }

    /**
     * Get product url model
     *
     * @return Testimonial_MageDoc_Model_Url
     */
    public function getUrlModel()
    {
        if ($this->_urlModel === null) {
            $this->_urlModel = Mage::getSingleton('magedoc/url');
        }
        return $this->_urlModel;
    }
}

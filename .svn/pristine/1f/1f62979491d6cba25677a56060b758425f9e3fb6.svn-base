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
class Testimonial_MageDoc_Model_Manufacturer extends Mage_Core_Model_Abstract 
{
    const ENTITY = 'magedoc_manufacturer';

    protected function _construct()
    {
        $this->_init('magedoc/manufacturer');
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

    /**
     * Init index
     *
     * @return Testimonial_MageDoc_Model_Manufacturer
     */
    protected function _afterSave()
    {
        $result = parent::_afterSave();

        Mage::getSingleton('index/indexer')->processEntityAction(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
        return $result;
    }
}


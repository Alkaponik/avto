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
class Testimonial_MageDoc_Model_Tecdoc_ArtLookup extends Testimonial_MageDoc_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_artLookup');
    }

    public function getProductUrl($params = array())
    {
        return Mage::getSingleton('catalog/product')->setData($this->getData())->getUrlInStore($params);
    }
}


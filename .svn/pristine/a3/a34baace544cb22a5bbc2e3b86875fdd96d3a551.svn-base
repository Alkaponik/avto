<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Criteria
 *
 * @author Oleg
 */
class Testimonial_MageDoc_Block_Adminhtml_ArtLookup extends Testimonial_MageDoc_Block_ArtLookup
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/product/lookup.phtml')->setArea('frontend');
    }
    
    protected function _getUrlModelClass()
    {
        return 'adminhtml/url';
    }

    public function getFormKey()
    {
        return Mage::getSingleton('core/session')->getFormKey();
    }
}


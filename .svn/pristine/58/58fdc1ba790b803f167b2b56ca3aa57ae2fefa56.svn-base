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
class Testimonial_MageDoc_Block_Manufacturer_List extends Mage_Core_Block_Template
{
    const COLUMN_COUNT = 3;

    protected $_manufacturerCollection;
    
    public function __construct()
    {
        return parent::__construct();
    }

    public function getManufacturerCollection()
    {
        if (!isset($this->_manufacturerCollection)){
            
            $this->_manufacturerCollection = Mage::getResourceModel('magedoc/manufacturer_collection')
                    ->addEnabledFilter();
        }
        return $this->_manufacturerCollection;
    }
    
    public function getLogoUrl($manufacturer)
    {
        return $this->getBaseMediaUrl() . 'avtomarks/'. $manufacturer->getLogo();
    }
    
    public function getColumnCount()
    {
        return self::COLUMN_COUNT;
    }
    
    public function getManufacturerUrl($manufacturer)
    {
        return Mage::getUrl('magedoc/make/', array('id' => $manufacturer->getId()));
    }
    
    public function getBaseMediaUrl()
    {
        return Mage::getBaseUrl('media') . 'magedoc/';
    }
                
    public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
}

<?php

class Testimonial_MageDoc_Model_Tecdoc_Type extends Testimonial_MageDoc_Model_Abstract
{   
    protected function _construct()
    {
        $this->_init('magedoc/tecdoc_type');
    }

    public function __toString()
    {
        return $this->getMfaBrand().' '.
                $this->getModCdsText().' '.
                $this->getTypCdsText().' '.
                $this->getTypeText();
    }
    
    public function getModelName()
    {
        return $this->getModCdsText();
    }
    
    public function getTypeText()
    {
       $string = $this->getEngineVolume() .' '. $this->getTypFuelDesText()
                    . ($this->getTypHpFrom() ? ' '. $this->getTypHpFrom() .' '. Mage::helper('magedoc')->__('h.p.') : '')
                    . ($this->getEngCode() ? ' ('.$this->getEngCode().')' : '');
       return $string;
    }

    public function getProductionStartYear()
    {
        return substr($this->getTypPconStart(), 0, 4);
    }

    public function getProductionPeriod()
    {
        return Mage::helper('magedoc')->getProductionPeriod($this);
    }

    public function getEngineVolume()
    {
        return $this->getTypCcm()
            ? number_format(round($this->getTypCcm()/1000,1),1)
            : '';
    }

    public function getUrl()
    {
        return Mage::getUrl('magedoc/type/view', array('id' => $this->getId()));
    }

    public function getModel()
    {
        return Mage::getSingleton('magedoc/tecdoc_model')->factory($this->getTypModId());
    }

    public function getTitle()
    {
        return $this->getModel()->getTitle()
            . ' ' . $this->getTypCdsText()
            . ' ' . $this->getTypeText();
    }

    public function getMetaTitle()
    {
        return $this->getTitle();
    }
}

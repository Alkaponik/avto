<?php

class Testimonial_MageDoc_Model_Indexer_Tecdoc extends Mage_Index_Model_Indexer_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/indexer_tecdoc');
    }

    public function getName()
    {
        return Mage::helper('magedoc')->__('MageDoc TecDoc Index');
    }

    public function getDescription()
    {
        return Mage::helper('magedoc')->__('Rebuild TecDoc directory table indexes');
    }

    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }
}
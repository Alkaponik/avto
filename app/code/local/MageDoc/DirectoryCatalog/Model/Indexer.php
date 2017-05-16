<?php
class MageDoc_DirectoryCatalog_Model_Indexer extends Mage_Index_Model_Indexer_Abstract
{
    public function getName()
    {
        return Mage::helper('magedoc')->__('Update Catalog Directory Offer Links');
    }

    public function getDescription()
    {
        return Mage::helper('magedoc')->__('Updates Supplier Ids and Generic Article Ids of the offers');
    }

    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    public function reindexAll()
    {
        Mage::getSingleton('magedoc/directory')
            ->getDirectory(MageDoc_DirectoryCatalog_Model_Directory::CODE)
            ->updateDirectoryOfferLink();
        return $this;
    }
}
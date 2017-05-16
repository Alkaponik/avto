<?php
class Testimonial_MageDoc_Model_Retailer_Data_Import_Parser_Base
    extends Testimonial_MageDoc_Model_Retailer_Data_Import_Parser_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_parser_base');
    }

    public function linkOffersToDirectory( )
    {
        $this->getResource()->updateTdArtId();

        parent::linkOffersToDirectory();

        return $this;
    }

    public function insertIntoBaseTable( )
    {
        $this->prepareSourceAdapter();

        $this->_fillBaseTable();

        $this->getSession()->setLastPriceSheetRecordsWithOldBrands(
            $this->getResource()->updateBaseTableSupplierId()
        );

        return $this;
    }

    protected function _fillBaseTable( )
    {
        $insertedCount = 0;
        while ( ( $bunchSize = count($bunch = $this->getNextBunch(self::MAX_BUNCH_SIZE)) ) > 0 ) {
            $insertedCount += $this->getResource()->saveBunchToBase($bunch);
        }

        if( !$insertedCount ) {
            $retailerName = $this->getRetailer()->getName();
            $fileName = $this->getSource()->getSourcePath();

            Mage::throwException(
                Mage::helper('magedoc')->__('%s price (%s) contains no valid records', $retailerName, $fileName )
            );
        }
        $this->getSession()->setLastPriceSheetValidRecords($insertedCount);
        return $this;
    }

    public function insertImportRetailerDataPreview( $supMapIds = null, $parseSourceIntoBase = true )
    {
        /** @var Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Parser_Base  $modelResource */
        $modelResource = $this->getResource();

        if($parseSourceIntoBase) {
            $this->insertIntoBaseTable();
        }

        if( $keyFields = $this->getUpdateKeys() ) {
            $modelResource->updateByKey( $keyFields );
        } else {
            $modelResource->insertImportRetailerDataPreview ( $supMapIds );
        }

        return $this;
    }

    public function updatePreview( $supMapIds = null)
    {
        $parseSourceIntoBase = !$this->isSourceParsedIntoBase();
        if(!$parseSourceIntoBase) {
            $this->updateBaseTableSupplierId();
        }

        $this->insertImportRetailerDataPreview($supMapIds, $parseSourceIntoBase);
        $this->linkOffersToDirectory( );

        return $this;
    }
}
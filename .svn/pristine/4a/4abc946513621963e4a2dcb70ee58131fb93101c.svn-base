<?php
class Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Parser_Advanced
    extends Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Parser_Abstract
{
    public function saveBunchToPreview($data)
    {
        $helper = Mage::getResourceHelper('magedoc_system');

        $rowCount = $helper->insertMultipleOnDuplicate( $this->getTable('magedoc/import_retailer_data_preview'), $data['preview'],
            array(
                'name',
                'description',
                'code_normalized',
                'code_raw',
                'code',
                'cost',
                'price',
                'supplier_id',
                'qty',
                'domestic_stock_qty',
                'general_stock_qty',
                'distant_stock_qty',
                'other_stock_qty',
                'updated_at'
            )
        );

        return $rowCount;
    }

    public function updateByKey($keyFields)
    {
        return $this;
    }

    public function deleteSourceRecords( $sourceId )
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->delete($this->getTable('magedoc/import_retailer_data_base'), "source_id = $sourceId" );

        $previewTable = $this->getTable('magedoc/import_retailer_data_preview');
        $linkTable = $this->getTable('magedoc/directory_offer_link_preview');

        $sql = "DELETE link, preview FROM $previewTable as preview
            INNER JOIN $linkTable as link USING(data_id) WHERE `source_id` = ?";
        $adapter->query($sql, array($sourceId));

        return $this;
    }

    public function saveBunchToBase($data)
    {
        $adapter = $this->_getWriteAdapter();
        $rowCount = $adapter->insertMultiple($this->getTable('magedoc/import_retailer_data_base'), $data['base']);
        $lastInsertId = $adapter->lastInsertId();

        $extendedData = array();
        $counter = 1;
        foreach($data['extended'] as  $row) {
            $extendedData[] =
                array(
                    'data_id' => $lastInsertId + $counter - 1,
                    'data' => serialize($row)
                );
            $counter++;
        }

        $adapter->insertMultiple($this->getTable('magedoc/import_retailer_data_extended_base'), $extendedData);

        return $rowCount;
    }
}
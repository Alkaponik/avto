<?php
class MageDoc_DirectoryTire_Model_Resource_Parser
    extends Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Parser_Advanced
{
    protected $_brands = null;

    public function getBrands()
    {
        if(is_null($this->_brands)) {
            $query = "SELECT brand_id, brand_name, correct_brand_name_id FROM `tires_directory_1_0`.`tires_brands` ORDER BY iteration_priority DESC";
            $result = $this->_getReadAdapter()->query($query);

            while($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
                $this->_brands[$row['brand_id']] = $row;
            }
        }

        return $this->_brands;
    }

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
            ),
            true
        );

        return $rowCount;
    }
}
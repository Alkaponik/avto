<?php
class Testimonial_Avtoto_Model_Resource_Retailer_Data_Import_Vladislav
    extends Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Parser_Base
{
    protected function _getBaseTableSelect($fieldsForSelect)
    {
        $select = parent::_getBaseTableSelect($fieldsForSelect);
        $select->joinLeft(
            array('vladproductGroups' => $this->getTable('magedoc/vladislav_sPgr')),
            'main_table.description = vladproductGroups.kodpgr',
            false
        );
        return $select;
    }

    public function updateBaseTableSupplierId( $ids = null )
    {
        if ( is_array($ids) && empty($ids) ) {
            return 0;
        }

        $adapter = $this->_getWriteAdapter();
        $sql
            = <<<SQL
            UPDATE {$this->getTable('magedoc/import_retailer_data_base')} as base
            INNER JOIN {$this->getTable('magedoc/vladislav_sGr')} as vladBrands
                ON base.manufacturer_id = vladBrands.kodgr
            LEFT JOIN {$this->getTable('magedoc/supplier_map')} as supplier_map
                ON supplier_map.manufacturer = vladBrands.name
            SET base.manufacturer = vladBrands.name, base.supplier_id = supplier_map.supplier_id
            WHERE base.retailer_id = {$this->getRetailer()->getId()};
SQL;
        if(!is_null($ids)) {
            $sql .= " AND supplier_map.map_id IN (" . implode(',', $ids) . ")";
        }

        $result = $adapter->query($sql);
        return $result->rowCount();
    }

}
<?php
abstract class Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Parser_Abstract
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_retailer;
    protected $_importAdapterConfig;
    protected $_source;
    protected $_model;

    protected function _construct()
    {
        $this->_init('magedoc/import_retailer_data_base', 'data_id');
    }


    public function setModel( Testimonial_MageDoc_Model_Retailer_Data_Import_Parser_Abstract $model  )
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * @return Testimonial_MageDoc_Model_Retailer_Data_Import_Parser_Abstract
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @return Testimonial_MageDoc_Model_Retailer
     */
    public function getRetailer()
    {
        return $this->getModel()->getRetailer();
    }


    public function getImportAdapterConfig()
    {
        return $this->getModel()->getImportAdapterConfig();
    }

    public function getSource()
    {
        return $this->getModel()->getSource();
    }


    public function saveBunchToBase($data)
    {
        $adapter = $this->_getWriteAdapter();
        return $adapter->insertMultiple($this->getTable('magedoc/import_retailer_data_base'), $data);
    }

    public function prepareBaseTable()
    {
        $connection = $this->_getWriteAdapter();
        $base = $this->getTable('magedoc/import_retailer_data_base');

        $sql = <<<SQL
            DELETE FROM $base
              WHERE retailer_id = '{$this->getRetailer()->getId()}';
SQL;
        $connection->query($sql);

        return $this;
    }

    public function preparePreviewTable( $supMapIds = null )
    {
        if(is_array($supMapIds) && empty($supMapIds)) {
            return 0;
        }

        $preview = $this->getTable('magedoc/import_retailer_data_preview');
        $link = $this->getTable('magedoc/directory_offer_link_preview');

        $adapter = $this->_getWriteAdapter();

        $joinClause = '';
        if(!empty($supMapIds)) {
            $supMapIdsStr = implode(',', $supMapIds);
            $joinClause = <<<SQL
              INNER JOIN {$this->getTable('magedoc/supplier_map')} as supplier_map
                ON main_table.manufacturer = supplier_map.manufacturer AND map_id IN ({$supMapIdsStr})
SQL;
        }
        $query = <<<SQL
            DELETE main_table, link FROM $preview as main_table
              LEFT JOIN $link as link USING(data_id)
              {$joinClause}
              WHERE main_table.retailer_id = '{$this->getRetailer()->getId()}';
SQL;


        $adapter->query($query);

        return $this;
    }

    public function isSourceParsedIntoBase()
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            array('main_table' => $this->getTable('magedoc/import_retailer_data_base')),
            array('cc' => new Zend_Db_Expr(1))
        )->where('source_id = ?', $this->getSource()->getId())
        ->limit(1);

        $result = $adapter->query($select);
        $result = $result->fetch();

        return $result;
    }

    public function updateBaseTableSupplierId( $ids = null )
    {
        if ( is_array($ids) && empty($ids) ) {
            return 0;
        }

        $mainTableName = $this->getTable('magedoc/import_retailer_data_base');
        $adapter = $this->_getWriteAdapter();
        $sql
            = <<<SQL
            UPDATE {$mainTableName} as tmp
                INNER JOIN {$this->getTable('magedoc/supplier_map')} as supplier_map
                    ON tmp.manufacturer = supplier_map.manufacturer AND tmp.retailer_id = supplier_map.retailer_id
                SET tmp.supplier_id = supplier_map.supplier_id
                WHERE tmp.retailer_id = {$this->getRetailer()->getId()}
SQL;
        if(!is_null($ids)) {
            $sql .= " AND supplier_map.map_id IN (" . implode(',', $ids) . ")";
        }

        $result = $adapter->query($sql);
        return $result->rowCount();
    }

    abstract function updateByKey($keyFields);

    public function importBrands()
    {
        $adapter = $this->_getWriteAdapter();

        $missingSuppliers = $adapter->select()
            ->from(
                array('main_table' => $this->getTable('magedoc/import_retailer_data_preview')),
                array('main_table.manufacturer', 'main_table.retailer_id')
            )
            ->joinInner(
                array('directory_offer_link' => $this->getTable('magedoc/directory_offer_link_preview')),
                "main_table.data_id = directory_offer_link.data_id
                    AND directory_offer_link.directory_code = '{$this->getModel()->getDirectoryCode()}'",
                array('directory_code')
            )->joinLeft(
                array('supplier_map' => $this->getTable('magedoc/supplier_map')),
                "main_table.manufacturer = supplier_map.manufacturer
                    AND main_table.retailer_id = supplier_map.retailer_id AND supplier_map.directory_code = '{$this->getModel()->getDirectoryCode()}'",
                ''
            )
            ->where("supplier_map.manufacturer IS NULL AND main_table.source_id = {$this->getSource()->getId()}")
            ->group('main_table.manufacturer');

        $insert = $adapter->insertFromSelect(
            $missingSuppliers,
            $this->getTable('magedoc/supplier_map'),
            array('manufacturer', 'retailer_id', 'directory_code')
        );

        $rowCount = $adapter->query($insert)->rowCount();
        $this->getModel()->getDirectoryModel()->updateSupplierIdInSupplierMap(
            $this->getRetailer()->getId()
        );

        return $rowCount;
    }
}

<?php
class Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_retailer = null;

    protected function _construct()
    {
        $this->_init('magedoc/import_retailer_data_base', 'data_id');
    }

    protected function getRetailer()
    {
        return $this->_retailer;
    }

    public function setRetailer( $retailer )
    {
        $this->_retailer = $retailer;
        return $this;
    }

    public function importPrice()
    {
        $flushLimit = Mage::helper('magedoc')->getImportFlushLimit();
        $useTmpTable = $flushLimit
            && $this->getRetailer()->getActiveSession()->getValidRecords() > $flushLimit;
        if ($useTmpTable){
            $tableName = $this->getTable('magedoc/import_retailer_data_tmp');
            $this->_copyTablesForUpdate();
        } else {
            $tableName = $this->getTable('magedoc/import_retailer_data');
        }

        $this->_resetRetailerSuppliesQty($tableName);
        $this->_deleteImportRetailerDataDuplicates($tableName);

        $this->_updateCopiedData($useTmpTable);

        if ($useTmpTable){
            $this->_updatePriceTables();
        }

        Mage::getModel('magedoc/retailer')
            ->load( $this->getRetailer()->getId() )
            ->setLastImportDate( date('Y-m-d H:i:s') )
            ->save();
    }

    protected function _updatePriceTables()
    {
        $this->_replaceTableWith(
            $this->getTable('magedoc/import_retailer_data'),
            $this->getTable('magedoc/import_retailer_data_tmp')
        );

        /**
         * Temporarily don't copy large directory_offer_link table
         */
        /*$this->_replaceTableWith(
            $this->getTable('magedoc/directory_offer_link'),
            $this->getTable('magedoc/directory_offer_link_tmp')
        );*/

        $this->_replaceTableWith(
            $this->getTable('magedoc/import_retailer_data_extended'),
            $this->getTable('magedoc/import_retailer_data_extended_tmp')
        );
    }

    protected function _replaceTableWith( $current, $new )
    {
        $adapter = $this->_getWriteAdapter();

        $adapter->dropTable($current);
        $adapter->renameTable($new, $current);
    }

    protected function _copyTablesForUpdate()
    {
        $this->_copyTable(
            $this->getTable('magedoc/import_retailer_data'),
            $this->getTable('magedoc/import_retailer_data_tmp')
        );

        /**
         * Temporarily don't copy large directory_offer_link table
         */
        /*$this->_copyTable(
            $this->getTable('magedoc/directory_offer_link'),
            $this->getTable('magedoc/directory_offer_link_tmp')
        );*/

        $this->_copyTable(
            $this->getTable('magedoc/import_retailer_data_extended'),
            $this->getTable('magedoc/import_retailer_data_extended_tmp')
        );
    }


    protected function _copyTable( $old, $new )
    {
        $adapter = $this->_getWriteAdapter();

        $adapter->dropTable($new);
        $table = $adapter->createTableByDdl($old, $new);
        $adapter->createTable($table);

        $importRetailerDataTableSelect = $adapter->select()->from($old);
        $insertQuery = $adapter->insertFromSelect($importRetailerDataTableSelect, $new);
        $adapter->disableTableKeys($new);
        $adapter->query($insertQuery);
        $adapter->enableTableKeys($new);
    }

    protected function _resetRetailerSuppliesQty($tableName)
    {
        $currentDate = date('Y-m-d H:i:s');
        $adapter = $this->_getWriteAdapter();
        $adapter->update(
            $tableName,
            array(
                 'qty' => 0,
                 'domestic_stock_qty' => 0,
                 'general_stock_qty' => 0,
                 'other_stock_qty' => 0,
                 'distant_stock_qty' => 0,
                 'updated_at'        => new Zend_Db_Expr("'$currentDate'")
            ),
            array(
                 'retailer_id = ?' => $this->getRetailer()->getId()
            )
        );
    }

    protected function _deleteImportRetailerDataDuplicates($tableName = null)
    {
        if (is_null($tableName)){
            $tableName = $this->getTable('magedoc/import_retailer_data_tmp');
        }
        $query = "DELETE ird FROM `{$this->getTable('magedoc/import_retailer_data_preview')}` as irdp
                    INNER JOIN `{$tableName}` as ird
                      ON ird.code_normalized = irdp.code_normalized AND ird.manufacturer = irdp.manufacturer
                        AND ird.retailer_id = irdp.retailer_id
                    INNER JOIN `{$tableName}` as ird2
                      ON ird2.code_raw = irdp.code_raw AND ird2.manufacturer = irdp.manufacturer
                        AND ird2.retailer_id = irdp.retailer_id
                        AND ird.data_id <> ird2.data_id
                    WHERE irdp.retailer_id = {$this->getRetailer()->getId()};";

        $adapter = $this->_getWriteAdapter();
        $adapter->query($query);

        return $this;
    }

    protected function _updateCopiedData($useTmpTable = false)
    {
        /**
         * Temporarily don't copy large directory_offer_link table
         */
        $this->_prepareDirectoryOfferTmp(false && $useTmpTable)
            ->_updateImportRetailerDataTmp($useTmpTable)
            ->_updateDirectoryOfferTmp(false && $useTmpTable)
            ->_updateDataExtendedTmp($useTmpTable);

        return $this;
    }

    /**
     * Removes link records which are not present in preview table
     * Do we really need to remove them?
     * @return $this
     */

    protected function _prepareDirectoryOfferTmp($useTmpTable = false)
    {
        $linkTable = $useTmpTable
            ? $this->getTable('magedoc/directory_offer_link_tmp')
            : $this->getTable('magedoc/directory_offer_link');
        $dataTable = $useTmpTable
            ? $this->getTable('magedoc/import_retailer_data_tmp')
            : $this->getTable('magedoc/import_retailer_data');
         $sql = <<<SQL
          DELETE directory_offer_link_tmp FROM {$linkTable} as directory_offer_link_tmp
            INNER JOIN {$dataTable} as import_retailer_data_tmp
              ON import_retailer_data_tmp.data_id = directory_offer_link_tmp.data_id
            LEFT JOIN  {$this->getTable('magedoc/import_retailer_data_preview')} as import_retailer_data_preview
              ON import_retailer_data_preview.code_normalized = import_retailer_data_tmp.code_normalized AND
                import_retailer_data_preview.manufacturer = import_retailer_data_tmp.manufacturer AND
                import_retailer_data_preview.retailer_id = import_retailer_data_tmp.retailer_id
            WHERE import_retailer_data_tmp.retailer_id = {$this->getRetailer()->getId()}
              AND import_retailer_data_preview.data_id IS NULL
SQL;

        $this->_getWriteAdapter()->query($sql);

        return $this;
    }

    protected function _updateImportRetailerDataTmp($useTmpTable = false)
    {
        $dataTable = $useTmpTable
            ? $this->getTable('magedoc/import_retailer_data_tmp')
            : $this->getTable('magedoc/import_retailer_data');
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->getTable('magedoc/import_retailer_data_preview'))
            ->where('retailer_id = ? AND qty > 0', $this->getRetailer()->getId());

        $columns = array('data_id' => new Zend_Db_Expr('null'));
        $describe = $this->_getReadAdapter()->describeTable($this->getTable('magedoc/import_retailer_data_preview'));
        foreach ($describe as $column) {
            if ($column['PRIMARY'] === false) {
                $columns[] = $column['COLUMN_NAME'];
            }
        }
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns($columns);
        $insert = Mage::getResourceHelper('magedoc_system')->insertFromSelectOnDuplicate(
            $select,
            $dataTable,
            array(),
            array(
                 'name',
                 'description',
                 'code_normalized',
                 'code_raw',
                 'code',
                 'model',
                 'model_normalized',
                 'cost',
                 'price',
                 'delivery_days',
                 'currency',
                 'msrp',
                 'qty',
                 'domestic_stock_qty',
                 'general_stock_qty',
                 'distant_stock_qty',
                 'other_stock_qty',
                 'updated_at',
                 'td_art_id'
            )
        );

        $this->_getWriteAdapter()->query($insert);
        return $this;
    }

    protected function _updateDirectoryOfferTmp($useTmpTable = false)
    {
        $dataTable = $useTmpTable
            ? $this->getTable('magedoc/import_retailer_data_tmp')
            : $this->getTable('magedoc/import_retailer_data');
        $linkTable = $useTmpTable
            ? $this->getTable('magedoc/directory_offer_link_tmp')
            : $this->getTable('magedoc/directory_offer_link');
        $select = $this->_getReadAdapter()
            ->select()
            ->from(
                array('directory_offer_link_preview' => $this->getTable('magedoc/directory_offer_link_preview')),
                array( 'import_retailer_data_tmp.data_id', 'directory_code', 'supplier_id', 'directory_entity_id')
            )->joinInner(
                array('import_retailer_data_preview' => $this->getTable('magedoc/import_retailer_data_preview')),
                'import_retailer_data_preview.data_id = directory_offer_link_preview.data_id',
                ''
            )->joinLeft(
                array('import_retailer_data_tmp' => $dataTable),
                'import_retailer_data_tmp.code_normalized = import_retailer_data_preview.code_normalized
                    AND import_retailer_data_tmp.manufacturer = import_retailer_data_preview.manufacturer
                    AND import_retailer_data_tmp.retailer_id = import_retailer_data_preview.retailer_id',
                ''
            )->where('import_retailer_data_preview.retailer_id = ?', $this->getRetailer()->getId());


        $insert = $this->_getWriteAdapter()->insertFromSelect(
            $select,
            $linkTable,
            array( 'data_id', 'directory_code', 'supplier_id', 'directory_entity_id'),
            Varien_Db_Adapter_Pdo_Mysql::INSERT_ON_DUPLICATE
        );

        $this->_getWriteAdapter()->query($insert);

        return $this;
    }

    protected function _updateDataExtendedTmp($useTmpTable = false)
    {
        $dataTable = $useTmpTable
            ? $this->getTable('magedoc/import_retailer_data_tmp')
            : $this->getTable('magedoc/import_retailer_data');
        $extendedDataTable = $useTmpTable
            ? $this->getTable('magedoc/import_retailer_data_extended_tmp')
            : $this->getTable('magedoc/import_retailer_data_extended');
        $select = $this->_getReadAdapter()
            ->select()
            ->from(
                array('base' => $this->getTable('magedoc/import_retailer_data_base')),
                array()
            )->joinInner(
                array('extended_base' => $this->getTable('magedoc/import_retailer_data_extended_base')),
                'base.data_id = extended_base.data_id',
                array('data')
            )->joinLeft(
                array('tmp' => $dataTable),
                'base.retailer_id = tmp.retailer_id
                    AND base.code_raw = tmp.code_raw
                    AND base.manufacturer = tmp.manufacturer',
                array('extended_data_id' => 'data_id')
            )->where('base.retailer_id = ?', $this->getRetailer()->getId());;

        $adapter = $this->_getWriteAdapter();

        $insert = $adapter->insertFromSelect(
            $select,
            $extendedDataTable,
            array('data', 'data_id'),
            Varien_Db_Adapter_Pdo_Mysql::INSERT_ON_DUPLICATE
        );

        $adapter->query($insert);

        return $this;
    }

    public function getLinkedSuppliersCount( $directory )
    {
        $adapter = $this->_getWriteAdapter();
        $select = $this->_getPreviewTableRetailerManufacturersSelect()
            ->joinInner(
                array('directory_offer_link' => $this->getTable('magedoc/directory_offer_link_preview')),
                "main_table.data_id = directory_offer_link.data_id AND directory_offer_link.directory_code = '$directory'"
            )
            ->where('directory_offer_link.supplier_id IS NOT NULL');

        return $adapter->query($select)->rowCount();
    }

    public function getRetailerTotalBrandsCount( )
    {
        $adapter = $this->_getWriteAdapter();
        $select = $this->_getPreviewTableRetailerManufacturersSelect( );
        return $adapter->query($select)->rowCount();
    }

    public function getNotLinkedSuppliersCount($directory)
    {
        $tecdocResource = Mage::getResourceSingleton('magedoc/tecdoc_article');

        $adapter = $this->_getWriteAdapter();
        $select = $this->_getPreviewTableRetailerManufacturersSelect($this->getRetailer()->getId())
            ->joinLeft (
                array('supplier_map' => $tecdocResource->getTable('magedoc/supplier_map')),
                "main_table.manufacturer = supplier_map.manufacturer AND main_table.retailer_id = supplier_map.retailer_id
                  AND supplier_map.directory_code = '$directory'",
                ''
            )->where('map_id IS NULL');

        return $adapter->query($select)->rowCount();
    }

    public function getRecordsLinkedToDirectoryCount( )
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->
            select()
            ->from(
                array( 'main_table' => $this->getTable('magedoc/import_retailer_data_preview')),
                array('cc' => 'count(*)')
            )
            ->joinInner(
                array('directory_offer_link' => $this->getTable('magedoc/directory_offer_link_preview')),
                'main_table.data_id = directory_offer_link.data_id'
            )
            ->where(
                'directory_offer_link.directory_entity_id IS NOT NULL AND main_table.retailer_id = ?',
                $this->getRetailer()->getId()
            );

        $result = $adapter->query($select)->fetch();
        return $result['cc'];
    }

    protected function _getPreviewTableRetailerManufacturersSelect()
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from (
                array('main_table' => $this->getTable('magedoc/import_retailer_data_preview')),
                array('count(*) as cc')
            )->where('main_table.retailer_id = ?', $this->getRetailer()->getId())->group('main_table.manufacturer');

        return $select;
    }

    public function getPriceRecordsLinkedToSupplier()
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter
            ->select()
            ->from(
                array('main_table' => $this->getTable('magedoc/import_retailer_data_preview')),
                array('cc'=>'count(*)')
            )
            ->where('supplier_id IS NOT NULL AND retailer_id = ?', $this->getRetailer()->getId());

        $result = $adapter->query($select)->fetch();

        return $result['cc'];
    }

}
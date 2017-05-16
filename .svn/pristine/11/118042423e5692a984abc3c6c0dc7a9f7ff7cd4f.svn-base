<?php
class Testimonial_Avtoto_Model_Resource_Price extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_shopDbName = null;
    protected $_shopAdapter = null;

    protected function _construct()
    {
        $this->_init('avtoto/import_retailer', 'data_id');
        $adapter = $this->_getWriteAdapter();
        $adapter->query('SET SESSION wait_timeout = 600;');
    }

    public function getShopDbName()
    {
        if (is_null($this->_shopDbName)) {
            $this->_shopDbName = (string)Mage::getConfig()->getNode('global/avtoto/shop_database/db_name');
        }

        return $this->_shopDbName;
    }

    protected function _getShopAdapter()
    {
        if (is_null($this->_shopAdapter)) {
            $adapter = $this->_getWriteAdapter();
            $config = $adapter->getConfig();
            $config['dbname'] = $this->getShopDbName();
            $this->_shopAdapter = new Varien_Db_Adapter_Pdo_Mysql($config);
            $this->_shopAdapter->query('SET SESSION wait_timeout = 600;');
        }

        return $this->_shopAdapter;
    }

    public function updateShopRetailersTable()
    {
        $shopRetailerTable = $this->getTable('avtoto/retailer');
        $sql
            = <<<SQL
            UPDATE {$shopRetailerTable} as r1
              INNER JOIN {$this->getTable('magedoc/retailer')} as r2 USING (retailer_id)
              SET r1.rate = r2.rate, r1.margin_ratio = r2.margin_ratio, r1.is_import_enabled = r2.is_import_enabled,
              r1.use_for_autopricing = r2.use_for_autopricing, r1.fixed_fee = r2.fixed_fee,
              r1.stock_status = r2.stock_status, r1.enabled = r2.enabled;
SQL;
        $adapter = $this->_getWriteAdapter();
        $adapter->query($sql);

        $insertQuery
            = <<<SQL
            INSERT INTO {$shopRetailerTable} SELECT  `main_table`.`retailer_id`,  `main_table`.`name`,
                'Avtoto_Import_Default',  `main_table`.`rate`,  `main_table`.`sort_order`,
                `main_table`.`is_update_enabled` ,  `main_table`.`session_id`,  `main_table`.`last_import_date`,
                `main_table`.`is_import_enabled` ,  `main_table`.`use_for_autopricing` ,  `main_table`.`margin_ratio` ,
                `main_table`.`fixed_fee`, `main_table`.`enabled`,  `main_table`.`stock_status`,
                `main_table`.`discount_table`, `main_table`.`margin_table`
                FROM  {$this->getTable('magedoc/retailer')} AS  `main_table`
                LEFT JOIN  {$shopRetailerTable} AS  `shop_retailer` ON shop_retailer.retailer_id = main_table.retailer_id
                WHERE shop_retailer.retailer_id IS NULL
SQL;
        $adapter->query($insertQuery);

        $sql
            = <<<SQL
            UPDATE {$shopRetailerTable} as r1
              LEFT JOIN {$this->getTable('magedoc/retailer')} as r2 USING (retailer_id)
              SET r1.enabled = 0
              WHERE r2.retailer_id IS NULL AND r1.retailer_id < 1000;
SQL;
        $adapter->query($sql);
    }

    public function updateShopPriceTable()
    {
        $this->_createTmpTable(
            $this->getTable('avtoto/import_retailer_tmp', true),
            $this->getTable('magedoc/import_retailer_data')
        );

        $this->_copyDataFromTable(
            $this->getTable('avtoto/import_retailer_tmp'),
            $this->getTable('magedoc/import_retailer_data')
        );

        $this->_fillProductIdsInTheTmpPriceTable();

        $this->_replaceTableWith(
            $this->getTable('avtoto/import_retailer', true),
            $this->getTable('avtoto/import_retailer_tmp', true)
        );

        $this->_createTmpTable(
            $this->getTable('avtoto/directory_offer_link_tmp', true),
            $this->getTable('magedoc/directory_offer_link')
        );

        $this->_copyDataFromTable(
            $this->getTable('avtoto/directory_offer_link_tmp'),
            $this->getTable('magedoc/directory_offer_link'),
            'directory_code = \'tires\''
        );

        $this->_replaceTableWith(
            $this->getTable('avtoto/directory_offer_link', true),
            $this->getTable('avtoto/directory_offer_link_tmp', true)
        );
    }


    protected function _createTmpTable( $tmpTableName, $sourceTableName )
    {
        /** @var Varien_Db_Adapter_Pdo_Mysql $adapter */
        $adapter = $this->_getWriteAdapter();
        $table = $adapter->createTableByDdl($sourceTableName, $tmpTableName);
        $shopAdapter = $this->_getShopAdapter();

        $shopAdapter->dropTable($tmpTableName);
        $shopAdapter->createTable($table);
    }


    protected function _copyDataFromTable($table, $sourceTable, $whereCondition = null)
    {
        /** @var Varien_Db_Adapter_Pdo_Mysql $adapter */
        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select()->from($sourceTable);
        if (!is_null($whereCondition)){
            $select->where($whereCondition);
        }

        $shopPriceTable = $table;
        $insert = $adapter->insertFromSelect($select, $shopPriceTable);
        $adapter->disableTableKeys($shopPriceTable);
        $adapter->query($insert);
        $adapter->enableTableKeys($shopPriceTable);
    }

    protected function _replaceTableWith($targetTable, $newTable)
    {
        /** @var Varien_Db_Adapter_Pdo_Mysql $adapter */
        $adapter = $this->_getShopAdapter();

        $adapter->dropTable($targetTable);
        $adapter->renameTable($newTable, $targetTable);
    }

    protected function _fillProductIdsInTheTmpPriceTable()
    {
        $sql
            = <<<SQL
            UPDATE {$this->getTable('avtoto/import_retailer_tmp')} SET import_retailer_tmp.product_id = NULL;

            UPDATE {$this->getTable('avtoto/import_retailer_tmp')} as import_retailer_tmp
              INNER JOIN {$this->getTable('avtoto/import_retailer')} as price
                ON price.data_id = import_retailer_tmp.data_id
              SET import_retailer_tmp.product_id = price.product_id;

            UPDATE {$this->getTable('avtoto/import_retailer_tmp')} as import_retailer_tmp
               INNER JOIN {$this->getTable('avtoto/product')} as product ON product.ART_ID = import_retailer_tmp.td_art_id
                 SET import_retailer_tmp.product_id = product.productID

SQL;
        $adapter = $this->_getShopAdapter();
        $adapter->query($sql);
    }

    public function updateCatalogFromShop()
    {
        $adapter = $this->_getWriteAdapter();
        $tecDocResource = Mage::getResourceSingleton('magedoc/tecdoc_article');

        $resource = Mage::getModel('catalog/product')->getResource();
        $productTable = $resource->getEntityTable();
        $priceAttribute = $resource->getAttribute('price');
        $costAttribute = $resource->getAttribute('cost');
        $priceTable = $priceAttribute->getBackendTable();

        $sql = <<<SQL
          UPDATE `{$productTable}` AS `main_table`
            INNER JOIN {$tecDocResource->getTable('magedoc/tecdoc_article')} AS `td_article`
              ON td_article.ART_ID = main_table.td_art_id
            LEFT JOIN {$priceTable} AS `catalog_product_price`
              ON catalog_product_price.entity_id = main_table.entity_id
                AND catalog_product_price.attribute_id = {$priceAttribute->getId()}
                AND catalog_product_price.store_id = 0
            LEFT JOIN `catalog_product_entity_decimal` AS `catalog_product_cost`
              ON catalog_product_cost.entity_id = main_table.entity_id
                AND catalog_product_cost.attribute_id = {$costAttribute->getId()}
                AND catalog_product_cost.store_id = 0
            INNER JOIN {$this->getTable('avtoto/product')} as p2
              ON td_article.ART_ID = p2.ART_ID
              SET catalog_product_price.value = Price, main_table.retailer_id = p2.retailer_id, catalog_product_cost.value = p2.cost;
SQL;
        $adapter->query($sql);
    }

    public function updateShopPrices()
    {
        $adapter = $this->_getWriteAdapter();
        $tecDocResource = Mage::getResourceSingleton('magedoc/tecdoc_article');

        $resource = Mage::getModel('catalog/product')->getResource();
        $productTable = $resource->getEntityTable();
        $priceAttribute = $resource->getAttribute('price');
        $costAttribute = $resource->getAttribute('cost');
        $priceTable = $priceAttribute->getBackendTable();
        $costTable = $costAttribute->getBackendTable();
        $stockItemTable = Mage::getResourceModel('cataloginventory/stock_item')->getMainTable();

        /**
         * @todo: Add stock qty and stock_status update
         */
        $sql = <<<SQL
          UPDATE {$this->getTable('avtoto/product')} AS p
          INNER JOIN `{$productTable}` AS `main_table`
            ON p.productID = main_table.avtoto_id
          INNER JOIN {$priceTable} AS `catalog_product_price`
              ON catalog_product_price.entity_id = main_table.entity_id
                AND catalog_product_price.attribute_id = {$priceAttribute->getId()}
                AND catalog_product_price.store_id = 0
          INNER JOIN {$costTable} AS `catalog_product_cost`
              ON catalog_product_cost.entity_id = main_table.entity_id
                AND catalog_product_cost.attribute_id = {$costAttribute->getId()}
                AND catalog_product_cost.store_id = 0
          INNER JOIN `{$stockItemTable}` AS stock_item
              ON stock_item.stock_id = 1
                AND stock_item.product_id = main_table.entity_id
          SET p.list_price = GREATEST(p.Price, p.list_price),
            p.Price = catalog_product_price.value,
            p.retailer_id = main_table.retailer_id,
            p.cost = catalog_product_cost.value,
            p.in_stock = stock_item.qty,
            p.is_in_stock = stock_item.is_in_stock;
SQL;
        Mage::log($sql);

        $adapter->query($sql);
    }

    public function getTable( $entity_name, $tableOnly = false )
    {
        $tableName = parent::getTable( $entity_name );

        if (!$tableOnly && strpos($entity_name, 'avtoto/') === 0){
            return $this->getShopDbName() . '.' . $tableName;
        }

        return $tableName;
    }
}

<?php
class MageDoc_DirectoryCatalog_Model_Resource_Processor
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/generic_article_map', 'ga_map_id');
        return $this;
    }

    public function addNewGenericArticlesToMap()
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('import_retailer_data' => $this->getTable('magedoc/import_retailer_data')),
                array(
                     new Zend_Db_Expr('NULL'),
                     'import_retailer_data.name',
                     new Zend_Db_Expr("
                     REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE(
                        REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( import_retailer_data.name, ' ', '' ) , '+', '' ) , '.', ''

                        ) , '-', '' ) , '=', '' ) , '\\\\', '' ) , '/', '' ) , '\\'', '' ) , '\"', '' ) , ')', '' ) , '(',

                     '' ) , ']', '' ) , '[', '' )
                     "),

                new Zend_Db_Expr(0),
                new Zend_Db_Expr("'catalog'"),
                new Zend_Db_Expr('NULL'),
                new Zend_Db_Expr("''"),
                new Zend_Db_Expr('COUNT( * ) AS cc'),
                new Zend_Db_Expr('1')
                )
            )->joinInner(
                array('r' => $this->getTable('magedoc/retailer')),
                'import_retailer_data.retailer_id = r.retailer_id AND r.enabled = 1',
                ''
            )
            ->joinLeft(
                array('supplier_map' => $this->getTable('magedoc/supplier_map')),
                'import_retailer_data.name = supplier_map.manufacturer',
                ''
            )->joinLeft(
                array('generic_article_map' => $this->getTable('magedoc/generic_article_map')),
                'generic_article_map.name = import_retailer_data.name',
                ''
            )
            ->where('import_retailer_data.name IS NOT NULL
                AND import_retailer_data.name != \'\'
                AND supplier_map.map_id IS NULL
                AND generic_article_map.ga_map_id IS NULL')
            ->group('import_retailer_data.name')
            ->having('COUNT(*) > ' .  Mage::getStoreConfig('magedoc/generic_article/product_names_count_assign'));


        $insert = $this->_getWriteAdapter()->insertFromSelect( $select, $this->getTable('magedoc/generic_article_map'));

        $this->_getWriteAdapter()->query($insert);

        if (Mage::helper('core')->isModuleEnabled('MageDoc_DirectoryTecdoc')) {
            $tdResource = Mage::getResourceSingleton('magedoc/tecdoc_article');
            $lngId = Mage::helper('magedoc')->getLngId();

            $query = "UPDATE {$this->getTable('magedoc/generic_article_map')} as gam
                INNER JOIN(
                  SELECT genericArticle.GA_ID as ga_id, ird.name as name FROM `{$this->getTable('magedoc/import_retailer_data')}` as ird
                    INNER JOIN `{$this->getTable('magedoc/retailer')}` as r ON r.retailer_id = ird.retailer_id AND r.enabled = 1
                    INNER JOIN `{$this->getTable('magedoc/directory_offer_link')}` as dof ON ird.data_id = dof.data_id AND dof.directory_code = 'tecdoc'
                    INNER JOIN {$tdResource->getTable('magedoc/tecdoc_linkArtGA')} as linkArtGa
                        ON dof.directory_entity_id = linkArtGa.LAG_ART_ID
                    LEFT JOIN {$tdResource->getTable('magedoc/tecdoc_genericArticle')} as genericArticle
                        ON linkArtGa.LAG_GA_ID = genericArticle.GA_ID
                    LEFT JOIN {$tdResource->getTable('magedoc/tecdoc_designation')} as designation
                        ON genericArticle.GA_DES_ID = designation.DES_ID AND designation.DES_LNG_ID = {$lngId}
                    LEFT JOIN {$tdResource->getTable('magedoc/tecdoc_desText')}
                        ON DES_TEX_ID = TEX_ID
                    LEFT JOIN `{$this->getTable('magedoc/des_text_template')}` as dtt ON designation.DES_TEX_ID = dtt.td_tex_id
                    LEFT JOIN `{$this->getTable('magedoc/supplier_map')}` as sm ON ird.name = sm.manufacturer
                  WHERE ird.name IS NOT null AND ird.name != '' AND sm.map_id IS NULL
                    GROUP BY ird.name
                    HAVING COUNT(DISTINCT linkArtGa.LAG_GA_ID) = 1
                  ) as t ON gam.name = t.name
                SET gam.generic_article_id = t.ga_id
                WHERE gam.generic_article_id IS NULL

          ";

            $this->_getWriteAdapter()->query($query);

            $query = "UPDATE {$this->getTable('magedoc/generic_article_map')} as gam
                    INNER JOIN {$tdResource->getTable('magedoc/tecdoc_desText')}
                      ON TEX_TEXT = gam.name
                    INNER JOIN {$tdResource->getTable('magedoc/tecdoc_designation')} as designation
                      ON DES_TEX_ID = TEX_ID/* AND designation.DES_LNG_ID = {$lngId}*/
                    LEFT JOIN `{$this->getTable('magedoc/des_text_template')}` as dtt
                      ON TEX_ID = dtt.td_tex_id
                    INNER JOIN {$tdResource->getTable('magedoc/tecdoc_genericArticle')} as genericArticle
                      ON genericArticle.GA_DES_ID = designation.DES_ID
                  SET gam.generic_article_id = genericArticle.GA_ID
                  WHERE gam.generic_article_id IS NULL
          ";

            $this->_getWriteAdapter()->query($query);
        }

        $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('update_directory_offer_link');
        if ($indexProcess) {
            $indexProcess->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }

        return $this;
    }

    public function addNewSuppliersToMap()
    {

        $connection = $this->_getReadAdapter();
        $select = $connection->select()
            ->from(
                array('supplier_map' => $this->getTable('magedoc/supplier_map')),
                array(
                     'manufacturer',
                     new Zend_Db_Expr("NULL"),
                     new Zend_Db_Expr("'catalog'"),
                     'retailer_id',
                     'use_crosses',
                     'code_delimiter',
                     'code_part_count',
                     'prefix_length',
                     'suffix_length',
                     'prefix',
                     'suffix',
                     'alias',
                     'discount_percent',
                     'created_at',
                ))
            ->leftJoin(
                array('catalog_supplier_map' =>  $this->getTable('magedoc/supplier_map')),
                'catalog_supplier_map.directory_code = \'catalog\'
                    AND supplier_map.retailer_id = catalog_supplier_map.retailer_id
                    AND supplier_map.manufacturer = catalog_supplier_map.manufacturer'
            )
            ->where('directory != \'catalog\' AND catalog_supplier_map.manufacturer IS NULL');

        $insert = $connection->insertFromSelect(
            $select,
            $this->getTable('magedoc/supplier_map'),
            array(
                 'manufacturer',
                 'supplier_id',
                 'directory_code',
                 'retailer_id',
                 'use_crosses',
                 'code_delimiter',
                 'code_part_count',
                 'prefix_length',
                 'suffix_length',
                 'prefix',
                 'suffix',
                 'alias',
                 'discount_percent',
                 'created_at',
            ),
            Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
        );

        $connection->query($insert);

        $manufacturerAttrId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer')->getId();

        if (Mage::helper('core')->isModuleEnabled('MageDoc_DirectoryTecdoc')) {
            $tdResource = Mage::getResourceSingleton('magedoc/tecdoc_article');

            $sql = <<<SQL
            UPDATE {$this->getTable('magedoc/supplier_map')} as supplier_map
              INNER JOIN {$tdResource->getTable('magedoc/tecdoc_supplier')} as supplier
                ON supplier.SUP_ID = supplier_map.supplier_id AND supplier_map.directory_code = 'tecdoc'
              INNER JOIN {$this->getTable('eav/attribute_option_value')} as attribute_option_value
                ON attribute_option_value.value = supplier.SUP_BRAND
              INNER JOIN {$this->getTable('eav/attribute_option')} as attribute_option
                ON attribute_option.option_id = attribute_option_value.option_id
                  AND attribute_option.attribute_id = '{$manufacturerAttrId}'
              INNER JOIN {$this->getTable('magedoc/supplier_map')} as supplier_map2
                ON supplier_map.retailer_id = supplier_map2.retailer_id AND supplier_map.manufacturer = supplier_map2.manufacturer AND
                  supplier_map2.directory_code = 'catalog'
              SET supplier_map2.supplier_id = attribute_option_value.option_id
SQL;
            $connection->query($sql);
        }

        $sql = <<<SQL
            UPDATE {$this->getTable('magedoc/supplier_map')} as supplier_map
              INNER JOIN {$this->getTable('eav/attribute_option_value')} as attribute_option_value
                ON attribute_option_value.value = supplier_map.manufacturer
              INNER JOIN {$this->getTable('eav/attribute_option')} as attribute_option
                ON attribute_option.option_id = attribute_option_value.option_id
                  AND attribute_option.attribute_id = '{$manufacturerAttrId}'
              SET supplier_map.supplier_id = attribute_option_value.option_id
              WHERE supplier_map.directory_code = 'catalog' AND supplier_map.supplier_id IS NULL
SQL;
        $connection->query($sql);

        $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('update_directory_offer_link');
        if ($indexProcess) {
            $indexProcess->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }

        return $this;
    }
}
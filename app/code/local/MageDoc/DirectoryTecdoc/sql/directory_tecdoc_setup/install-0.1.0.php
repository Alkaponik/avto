<?php

$installer = $this;
$installer->startSetup();

/* Index already exists */
//$conn->addIndex($installer->getTable('magedoc/tecdoc_artCriteria'), 'IDX_ACR_KV_DES_ID', 'ACR_KV_DES_ID');
$conn->addIndex($installer->getTable('magedoc/tecdoc_artCriteria'), 'IDX_ACR_CRI_ID', 'ACR_CRI_ID');
$conn->addIndex($installer->getTable('magedoc/tecdoc_model'), 'IDX_MOD_CDS_ID', 'MOD_CDS_ID');
$conn->addIndex($installer->getTable('magedoc/tecdoc_type'), 'IDX_TYP_CDS_ID', 'TYP_CDS_ID');
$conn->addIndex($installer->getTable('magedoc/tecdoc_type'), 'IDX_TYP_KV_ENGINE_DES_ID', 'TYP_KV_ENGINE_DES_ID');
$conn->addIndex($installer->getTable('magedoc/tecdoc_type'), 'IDX_TYP_KV_FUEL_DES_ID', 'TYP_KV_FUEL_DES_ID');
$conn->addIndex($installer->getTable('magedoc/tecdoc_type'), 'IDX_TYP_KV_BODY_DES_ID', 'TYP_KV_BODY_DES_ID');
$conn->addIndex($installer->getTable('magedoc/tecdoc_type'), 'IDX_TYP_KV_MODEL_DES_ID', 'TYP_KV_MODEL_DES_ID');
$conn->addIndex($installer->getTable('magedoc/tecdoc_type'), 'IDX_TYP_KV_AXLE_DES_ID', 'TYP_KV_AXLE_DES_ID');

$conn->addIndex($installer->getTable('magedoc/tecdoc_countryDesignation'), 'IDX_CDS_LNG_ID', 'CDS_LNG_ID');
/* Index already exists */
//$conn->addIndex($installer->getTable('magedoc/tecdoc_countryDesignation'), 'IDX_CDS_TEX_ID', 'CDS_TEX_ID');

$tableName = $installer->getTable('magedoc/tecdoc_linkGAStr');
$conn->addIndex($tableName, 'IDX_LGS_GA_ID', 'LGS_GA_ID');

/**
 * @todo check LGA_GRA_ID index necessity
 */

$idxField = 'LGA_GRA_ID';
$installer->getConnection()->addIndex(
    $installer->getTable('magedoc/tecdoc_linkGraArt'),
    $installer->getIdxName('magedoc/tecdoc_linkGraArt', $idxField),
    $idxField
);

$idxField = 'GRA_DOC_TYPE';
$installer->getConnection()->addIndex(
    $installer->getTable('magedoc/tecdoc_graphic'),
    $installer->getIdxName('magedoc/tecdoc_graphic', $idxField),
    $idxField
);

/** @todo Add index for LINK_ART_GA.LAG_SUP_ID */

$articleNormalizedTable = Mage::getResourceModel('magedoc/tecdoc_searchTree')->getTable('magedoc/tecdoc_articleNormalized');
$articleTable = Mage::getResourceModel('magedoc/tecdoc_searchTree')->getTable('magedoc/tecdoc_article');
$installer->run("
	     CREATE TABLE IF NOT EXISTS {$articleNormalizedTable}
            ( ARN_ART_ID INT(11) NOT NULL, ARN_ARTICLE_NR_NORMALIZED VARCHAR(66) NOT NULL, ARN_SUP_ID INT(11)  NOT
            NULL )
            ENGINE=MyISAM
            DEFAULT CHARACTER SET=utf8
            COLLATE=utf8_general_ci;

            INSERT INTO {$articleNormalizedTable} (
              SELECT ART_ID, REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                ART_ARTICLE_NR ,' ', ''), '+', ''), '.', ''), '-', ''), '=', ''), '\\\\', ''), '/', ''), '\\'', ''), '\"', ''), ')', ''), '(', ''), ']', ''), '[', ''), ART_SUP_ID FROM {$articleTable} )
	  ");

$connection = $installer->getConnection();

$connection->addIndex($articleNormalizedTable, $connection->getIndexName($articleNormalizedTable, 'ARN_ART_ID', Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY), 'ARN_ART_ID', Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY);
$connection->addIndex($articleNormalizedTable, $connection->getIndexName($articleNormalizedTable, array('ARN_SUP_ID','ARN_ARTICLE_NR_NORMALIZED','ARN_ART_ID'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX), array('ARN_SUP_ID','ARN_ARTICLE_NR_NORMALIZED','ARN_ART_ID'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);
$connection->addIndex($articleNormalizedTable, $connection->getIndexName($articleNormalizedTable, array('ARN_ARTICLE_NR_NORMALIZED','ARN_SUP_ID','ARN_ART_ID'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX), array('ARN_ARTICLE_NR_NORMALIZED','ARN_SUP_ID','ARN_ART_ID'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);

$installer->endSetup();

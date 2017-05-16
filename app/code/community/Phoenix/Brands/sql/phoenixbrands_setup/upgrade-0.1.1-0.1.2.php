<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();
/**
 * Create table 'catalog/category_product_idx'
 */
if (!$installer->tableExists('catalog/category_product_idx')) {
    $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog/category_product_idx'))
            ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
            ), 'Category ID')
            ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'default'   => '0',
            ), 'Product ID')
            ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'nullable'  => false,
                'default'   => '0',
            ), 'Position')
            ->addIndex($installer->getIdxName('catalog/category_product_idx', array('product_id')),
                array('product_id'))
            ->setComment('Catalog Product To Category Linkage Index Table');
    $installer->getConnection()->createTable($table);
}
$installer->endSetup();
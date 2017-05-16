<?php
/* @var MageDoc_DirectoryCatalog_Model_Resource_Setup $installer */

$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
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
        )
    );

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
$tdResource = Mage::getResourceSingleton('magedoc/tecdoc_article');

if (Mage::helper('core')->isModuleEnabled('MageDoc_DirectoryTecdoc')) {
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

Mage::getSingleton('directory_catalog/directory')->updateDirectoryOfferLink();

$installer->endSetup();
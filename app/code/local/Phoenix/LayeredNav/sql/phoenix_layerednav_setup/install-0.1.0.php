<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Db_Abstract */

$installer->getConnection()
    ->addColumn(
        $installer->getTable('catalog/eav_attribute'),
        'is_multiple_select_filter',
        array(
            'TYPE'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'LENGTH'    => 1,
            'UNSIGNED'  => true,
            'NULLABLE'  => false,
            'DEFAULT'   => 0,
            'COMMENT'   => 'Is Multiple Select Filter',
        )
    );

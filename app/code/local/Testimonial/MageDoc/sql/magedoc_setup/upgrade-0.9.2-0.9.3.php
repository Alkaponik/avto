<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$connection->addColumn($this->getTable('sales/order_grid'), 'margin',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'comment' => 'Margin',
        'scale'     => 4,
        'precision' => 12,
    ));

$query = <<<SQL
        UPDATE {$this->getTable('sales/order_grid')} as og
          INNER JOIN {$this->getTable('sales/order')} as o USING (entity_id)
          SET og.margin = o.margin;
SQL;

$connection->query($query);

$installer->endSetup();
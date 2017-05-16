<?php
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('shipping_multipletablerates')};
DELETE FROM {$this->getTable('core/config_data')} WHERE path like 'carriers/multipletablerates/%';
");

$installer->endSetup();

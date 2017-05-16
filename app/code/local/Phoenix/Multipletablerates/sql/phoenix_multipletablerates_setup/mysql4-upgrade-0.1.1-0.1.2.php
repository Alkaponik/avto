<?php

$this->startSetup();

$table = $this->getTable('shipping_multipletablerates');

if ($this->tableExists($table)){
    $this->getConnection()->addColumn($table,
    'sort', "int(5) NOT NULL DEFAULT 10");
}

$this->endSetup();
<?php

$this->startSetup();

$table = $this->getTable('shipping_multipletablerates');

if ($this->tableExists($table)){
   
    $this->run("
        ALTER TABLE `$table` CHANGE `condition_name` `condition_name` VARCHAR( 64 )
            CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
        );
}

$this->endSetup();
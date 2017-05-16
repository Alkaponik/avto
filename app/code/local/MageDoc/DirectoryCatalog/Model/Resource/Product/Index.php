<?php

class MageDoc_DirectoryCatalog_Model_Resource_Product_Index extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('directory_catalog/product_index', 'product_id');
    }
}
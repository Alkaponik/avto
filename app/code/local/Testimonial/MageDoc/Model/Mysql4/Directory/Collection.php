<?php
class Testimonial_MageDoc_Model_Mysql4_Directory_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/directory');
    }
}
<?php
class Testimonial_MageDoc_Model_Mysql4_Directory extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('magedoc/directory', 'directory_id');
    }

}
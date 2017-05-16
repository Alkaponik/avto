<?php
class Testimonial_MageDoc_Model_Mysql4_Import_Import extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('magedoc/import', 'data_id');
    }
}

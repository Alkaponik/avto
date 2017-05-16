<?php
class Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Session extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_serializableFields = array(
        //'price_source' => array(array(), array()),
        'messages' => array(array(), array()),
    );

    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_session', 'session_id');
    }
}



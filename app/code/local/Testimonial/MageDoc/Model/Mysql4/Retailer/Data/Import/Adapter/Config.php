<?php

class Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Adapter_Config extends Mage_Core_Model_Mysql4_Abstract
{

    protected $_serializableFields = array(
        'source_adapter_config' => array(array(), array()),
        'source_fields_map'     => array(array(), array()),
        'source_fields_filters'     => array(array(), array()),
    );

    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_adapter_config', 'config_id');
    }
}
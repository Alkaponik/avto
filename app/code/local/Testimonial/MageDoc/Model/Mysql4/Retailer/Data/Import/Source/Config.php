<?php

class Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Source_Config extends Mage_Core_Model_Mysql4_Abstract
{

    protected $_serializableFields = array(
        'source_settings' => array(array(), array()),
    );

    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_source_config', 'source_id');
    }

    public function unserializeFields(Mage_Core_Model_Abstract $object)
    {
        parent::unserializeFields($object);
        /*$sourceType = $object->getSourceType();
        $object->setData($sourceType, $object->getSourceSettings());
        $object->unsetData('source_settings');*/
        if(is_array($object->getSourceSettings())){
            foreach($object->getSourceSettings() as $k=>$v){
                $object->setData($k, $v);
            }
        }
        //$object->unsetData('source_settings');
    }
}
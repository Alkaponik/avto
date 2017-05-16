<?php

class Testimonial_CallBackRequest_Model_Resource_Request extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct(){
        $this->_init('callbackrequest/request', 'request_id');
    }

    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        $currentTime = Varien_Date::now();
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
            $object->setCreatedAt($currentTime);
        }else{
            $object->setUpdatedAt($currentTime);
        }
        $data = parent::_prepareDataForSave($object);
        return $data;
    }
}
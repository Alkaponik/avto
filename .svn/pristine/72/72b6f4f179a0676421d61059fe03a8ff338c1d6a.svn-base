<?php
class Testimonial_Intime_Model_Resource_Consignment extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct()
    {
        $this->_init('intime/consignment', 'consignment_id');
    }

    /**
     * Prepare data for save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return array
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        $currentTime = Varien_Date::now();
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
            $object->setCreatedAt($currentTime);
        }

        $object->setUpdatedAt($currentTime);
        return parent::_prepareDataForSave($object);
    }

}
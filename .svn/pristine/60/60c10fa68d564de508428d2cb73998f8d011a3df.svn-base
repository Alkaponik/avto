<?php

abstract class Testimonial_MageDoc_Model_Entity_Abstract extends Mage_Eav_Model_Entity_Abstract
{

    /**
     * Load entity's attributes into the object
     *
     * @param   Mage_Core_Model_Abstract $object
     * @param   integer $entityId
     * @param   array|null $attributes
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    public function load($object, $entityId, $attributes = array())
    {
        Varien_Profiler::start('__EAV_LOAD_MODEL__');
        /**
         * Load object base row data
         */
        $select  = $this->_getLoadRowSelect($object, $entityId);
        $row     = $this->_getReadAdapter()->fetchRow($select);

        if (is_array($row)) {
            $object->addData(array_change_key_case($row, CASE_LOWER));
        } else {
            $object->isObjectNew(true);
        }

        if (empty($attributes)) {
            $this->loadAllAttributes($object);
        } else {
            foreach ($attributes as $attrCode) {
                $this->getAttribute($attrCode);
            }
        }

        $this->_loadModelAttributes($object);

        $object->setOrigData();
        Varien_Profiler::start('__EAV_LOAD_MODEL_AFTER_LOAD__');

        $this->_afterLoad($object);
        Varien_Profiler::stop('__EAV_LOAD_MODEL_AFTER_LOAD__');

        Varien_Profiler::stop('__EAV_LOAD_MODEL__');
        return $this;
    }
    
}

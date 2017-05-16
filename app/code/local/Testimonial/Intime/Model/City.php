<?php
class Testimonial_Intime_Model_City extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('intime/city');
    }
    
    public function getOptionArray()
    {
        $options = array();

        $collection = $this->getCollection();
        while ($city = $collection->fetchItem()) {
            $options[$city->getId()] = $city->getName();
        }

        asort($options);

        return $options;
    }

    public function loadByName($name)
    {
        return $this->load($name, 'name');
    }
}

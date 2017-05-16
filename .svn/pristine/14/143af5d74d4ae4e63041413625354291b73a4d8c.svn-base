<?php

class Testimonial_MageDoc_Model_Source_Customer_Vehicle extends Testimonial_MageDoc_Model_Source_Abstract
{
    protected $_customerId = null;

    public function setCustomerId($customerId = null){
        $this->_customerId = $customerId;
        return $this;
    }

    public function getCustomerId(){
        return $this->_customerId;
    }

    public function getCollectionArray()
    {
        $options = array(
            array(
                'label' => 'Add New Vehicle',
                'value' => 0
        ));

        if(!is_null($this->getCustomerId())){
            $collection = Mage::getResourceModel('magedoc/customer_vehicle_collection');
            $collection->addFieldToFilter('customer_id', $this->getCustomerId());
            $collection->load();


            foreach ($collection as $vehicle) {
                $options[] = array(
                    'value' => $vehicle->getId(),
                    'label' => $vehicle->getManufacturer() . ' ' .
                        $vehicle->getModel() . ' ' .
                        $vehicle->getType() . ' ' .
                        $vehicle->getMileage() . ' ' .
                        $vehicle->getVin()
                );
            }
        }

        return $options;
    }

}
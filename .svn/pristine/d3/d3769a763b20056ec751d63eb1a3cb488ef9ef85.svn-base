<?php

class Testimonial_MageDoc_Model_Resource_Customer extends Mage_Customer_Model_Resource_Customer
{
    /**
     * Save customer addresses and set default addresses in attributes backend
     *
     * @param Varien_Object $customer
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _afterSave(Varien_Object $customer)
    {
        $this->_saveVehicles($customer);
        return parent::_afterSave($customer);
    }

    protected function _saveVehicles(Mage_Customer_Model_Customer $customer)
    {
        foreach ($customer->getVehicles() as $vehicle) {
            if ($vehicle->getData('_deleted')) {
                $vehicle->delete();
            } else {
                if(!$vehicle->getId()){
                    $vehicle->unsVehicleId();
                }
                $vehicle->setCustomerId($customer->getId())->save();

                if($quoteVehicle = $vehicle->getQuoteVehicle()){
                    $quoteVehicle->setCustomerVehicleId($vehicle->getId());
                    $quoteVehicle->setCustomerId($customer->getId());

                    if($orderVehicle = $quoteVehicle->getOrderVehicle()){
                        $orderVehicle->setCustomerVehicleId($vehicle->getId());
                    }
                }
            }
        }
        return $this;
    }

}

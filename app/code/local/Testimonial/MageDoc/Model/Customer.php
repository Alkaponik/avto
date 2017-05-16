<?php

class Testimonial_MageDoc_Model_Customer extends Mage_Customer_Model_Customer
{
    protected $_vehicles = null;
    protected $_vehiclesCollection = null;

    public function addVehicle($vehicle)
    {
        if(!$vehicle instanceof Testimonial_MageDoc_Model_Customer_Vehicle){
            if(!empty($vehicle['vehicle_id'])){
                $item = $this->getVehiclesCollection()->getItemById($vehicle['vehicle_id']);
                $item->addData($vehicle);
            }else{
                $model = Mage::getModel('magedoc/customer_vehicle');
                $model->addData($vehicle);
                $vehicle = $model;
                $this->getVehiclesCollection()->addItem($vehicle);
            }

        }elseif(!$vehicle->getId()){
            $this->getVehiclesCollection()->addItem($vehicle);
        }
        return $vehicle;
    }

    /**
     * Retrieve not loaded vehicles collection
     *
     * @return Testimonial_MageDoc_Model_Mysql4_Customer_Vehicle_Collection
     */
    public function getVehicleCollection()
    {
        return Mage::getResourceModel('magedoc/customer_vehicle_collection');
    }

    public function getVehiclesCollection()
    {
        if ($this->_vehiclesCollection === null) {
            $this->_vehiclesCollection = $this->getVehicleCollection()
                ->setCustomerFilter($this);
            foreach ($this->_vehiclesCollection as $vehicle) {
                $vehicle->setCustomer($this);
            }
        }

        return $this->_vehiclesCollection;
    }

    public function getVehicles()
    {
        $this->_vehicles = $this->getVehiclesCollection()->getItems();
        return $this->_vehicles;
    }

    public function getVehicleItemById($vehicleId)
    {
        return $this->getVehiclesCollection()->getItemById($vehicleId);
    }
}

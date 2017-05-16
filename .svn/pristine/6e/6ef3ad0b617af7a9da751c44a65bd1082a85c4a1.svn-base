<?php

class Testimonial_MageDoc_Model_Source_SuppliedType extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const SUPPLIED_TYPES_PATH = 'global/supply_status/types';
    const UNRESERVED = 'unreserved';
    const RESERVED = 'reserved';
    const WAREHOUSE_DELIVERY = 'warehouse_delivery';
    const SHIPPED = 'shipped';
    const PENDING_RETURN = 'pending_return';
    const RETURNED = 'returned';
    const SUPPLIER_RETURN_REGISTERED = 'supplier_return_registered';
    const RETURNED_TO_SUPPLIER = 'returned_to_supplier';
    const MOVE_TO_WAREHOUSE = 'move_to_warehouse';
    const MOVED_TO_WAREHOUSE = 'moved_to_warehouse';

    public function getAllOptions($withEmpty=true)
    {
        if (!$this->_options) {
            $this->_options = array();
            foreach (Mage::getConfig()->getNode(self::SUPPLIED_TYPES_PATH)->children() as $type) {
                $labelPath = self::SUPPLIED_TYPES_PATH . '/' . $type->getName() . '/label';
                $this->_options[] = array(
                    'label' => Mage::helper('magedoc')->__((string) Mage::getConfig()->getNode($labelPath)),
                    'value' => $type->getName()
                );
            }            
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array(
                'value'=>'',
                'label'=>Mage::helper('core')->__('-- Please Select --'))
            );
        }
        return $options;
    }

    public function toOptionArray()
    {
        $types = array();

        foreach (Mage::getConfig()->getNode(self::SUPPLIED_TYPES_PATH)->children() as $type) {
            $labelPath = self::SUPPLIED_TYPES_PATH . '/' . $type->getName() . '/label';
            $types[$type->getName()] = array(
                'label' => Mage::helper('magedoc')->__((string) Mage::getConfig()->getNode($labelPath)),
                'value' => $type->getName()
            );
        }

        return $types;
    }

    public function getOptionArray()
    {
        $optionArray = array();
        $options = $this->getAllOptions(false);
        foreach ($options as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }
}

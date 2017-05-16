<?php

class MageDoc_CRM_Model_Resource_Helper_Mysql4 extends Mage_Core_Model_Resource_Helper_Mysql4
{
    public function mergeCustomers($customerIdFrom, $customerIdTo)
    {
        $resource = Mage::getResourceSingleton('customer/customer');
        $connection = $this->_getWriteAdapter();
        $hlp = Mage::helper('magedoc_crm');

        foreach ($hlp->getCustomerRelatedEntities() as $entity){
            $fieldName = isset($entity['reference_field_name'])
                ? $entity['reference_field_name']
                : 'customer_id';
            $table = $resource->getTable($entity['table']);

            $connection->update(
                $table,
                array($fieldName => $customerIdTo),
                array("{$fieldName} = ?" => $customerIdFrom)
            );
        }
    }
}

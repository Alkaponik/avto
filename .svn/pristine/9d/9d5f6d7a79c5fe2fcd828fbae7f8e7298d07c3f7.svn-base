<?php

class MageDoc_CRM_Model_Observer
{
    public function customer_delete_before(Varien_Event_Observer $observer)
    {
        $hlp = Mage::helper('magedoc_crm');
        if (!$hlp->getCheckCustomerRelationsBeforeDelete()) {
            return;
        }
        $customer = $observer->getEvent()->getCustomer();
        $resource = $customer->getResource();
        $connection = $resource->getReadConnection();

        foreach ($hlp->getCustomerRelatedEntities() as $entity){
            $fieldName = isset($entity['reference_field_name'])
                ? $entity['reference_field_name']
                : 'customer_id';
            $table = $resource->getTable($entity['table']);
            $entity = $entity['label'];
            $select = $connection
                ->select()
                ->from($table)
                ->where("{$fieldName} = ?", $customer->getId())
                ->limit(1);

            $result = $connection->query($select);
            if ($result->rowCount()){
                Mage::throwException($hlp->__('Unable to delete customer with %s', $entity));
            }
        }
    }
}
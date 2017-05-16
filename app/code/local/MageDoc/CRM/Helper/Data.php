<?php

class MageDoc_CRM_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_XML_PATH_CUSTOMER_RELATED_ENTITIES = 'global/magedoc/customer_related_entities';
    const CONFIG_XML_PATH_CHECK_CUSTOMER_RELATIONS_BEFORE_DELETE = 'magedoc_crm/general/customer_relations_before_delete_check_enabled';

    public function getCheckCustomerRelationsBeforeDelete()
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_CHECK_CUSTOMER_RELATIONS_BEFORE_DELETE);
    }

    public function getCustomerRelatedEntities()
    {
        $valuePath = self::CONFIG_XML_PATH_CUSTOMER_RELATED_ENTITIES;
        return Mage::getConfig()->getNode($valuePath)->asCanonicalArray();
    }
}
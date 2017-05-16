<?php

class Testimonial_MageDoc_Model_Mysql4_Catalog_Layer_Filter_Type extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table name
     *
     */
    protected function _construct()
    {
        $this->_init('magedoc/type_product', 'product_id');
    }

    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $requestVar  = $filter->getRequestVar();
        $connection = $this->_getReadAdapter();
        $tableAlias = $requestVar . '_idx';
        $conditions = array(
            "{$tableAlias}.product_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.type_id IN (?)", $value)
        );

        $collection->getSelect()->joinLeft(
            array($tableAlias => $this->getMainTable()),
            implode(' AND ', $conditions),
            array()
        );

        $collection->getSelect()->where("e.td_art_id IS NULL OR {$tableAlias}.type_id IS NOT NULL");

        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
        $customerVehicles = array();
        $session = Mage::getSingleton('customer/session');
        $typeIds = $session->isLoggedIn()
            ? $session->getCustomer()->getVehicle()
                : null;
        if($session->getCustomerVehicle()){
            $customerVehicles = $session->getCustomerVehicle();
            if(!is_array($customerVehicles)){
                $customerVehicles = explode(',',$customerVehicles);
            }
            
            $maxResultCount = Mage::helper('magedoc')
                    ->getMaxCountSessionTypeIds();
            while(count($customerVehicles) > $maxResultCount){
                array_pop($customerVehicles);
            }

        }
        
        if (!$typeIds && empty($customerVehicles)){
            return array();
        }elseif($typeIds && !is_array($typeIds)){
                $typeIds = explode(',',$typeIds);
        }
                  
        if(empty($typeIds)){
            $typeIds = $customerVehicles;
        }else{
            $typeIds = array_merge($typeIds, $customerVehicles);
        }
        
        // clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $connection = $this->_getReadAdapter();
        $requestVar  = $filter->getRequestVar();
        $tableAlias = $requestVar . '_idx';
        $from = $select->getPart(Zend_Db_Select::FROM);
        if (isset($from[$tableAlias])){
            unset($from[$tableAlias]);
        }
        $select->setPart(Zend_Db_Select::FROM, $from);

        $conditions = array(
            "{$tableAlias}.product_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.type_id IN (?)", $typeIds)
        );

        $select
            ->join(
                array($tableAlias => $this->getMainTable()),
                join(' AND ', $conditions),
                array('type_id', 'count' => "COUNT({$tableAlias}.product_id)"))
            ->group("{$tableAlias}.type_id");

        return $connection->fetchPairs($select);
    }

    public function getProductTypes($product)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->getMainTable(), array('type_id'))
            ->where('product_id = :product_id');
        $bind = array('product_id' => (int)$product->getId());

        return  $this->_getWriteAdapter()->fetchAssoc($select, $bind);
    }
}

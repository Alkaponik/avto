<?php

class Testimonial_MageDoc_Model_Mysql4_Retailer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_retailersArray = array();
    
    
    protected function _construct()
    {
        $this->_init('magedoc/retailer');
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('retailer_id');
    }
    
    public function joinRetailers($collection = null, $joinAlias = 'main_table')
    {
        if(is_null($collection)){
            $collection = $this;
        }
        $collection->getSelect()
                ->joinInner(array('md_retailer' => $this->getTable('magedoc/retailer')),
                                        "md_retailer.retailer_id = {$joinAlias}.retailer_id", 
                                        array(''));                                                
        return $collection;
    }
    
    public function getRetailersArray()
    {
        $retailers = array();
        foreach($this as $item){
            $retailers[$item->getId()] = $item->getData();
        }
        return $retailers;
    }

    public function joinLastImportSession()
    {
        /**
         * $selectLast is needed to get last retailer session created_at
         */
        $selectLast = $this->getConnection()->select()
            ->from(
               $this->getTable('magedoc/retailer_data_import_session'),
                array('MAX(created_at) as created_at', 'retailer_id')
            )
            ->group('retailer_id');

        /**
         * $selectSessions is select to get sessions with known created_at datetime
         */
        $selectSessions = $this->getConnection()->select()
            ->from(
                array('session' => $this->getTable('magedoc/retailer_data_import_session')),
                array('status_id', 'session.retailer_id')
            )
            ->joinInner(
                array('last' => $selectLast),
                'last.created_at = session.created_at AND last.retailer_id = session.retailer_id',
                false
            );


        $this->getSelect()->joinLeft(
            array('retailer_session'=> $selectSessions),
            'main_table.retailer_id = retailer_session.retailer_id',
            array('status_id'=>'IFNULL(status_id, 0)')
        );
        $this->addFilterToMap('status_id', 'IFNULL(status_id,0)');

        return $this;
    }

    public function joinSupplyConfig()
    {
        $maxOrderTimeExpr = new Zend_Db_Expr('IF(order_hours_end IS NOT NULL, SUBSTRING_INDEX(order_hours_end, \' \', -1), \'00:00:00\')');
        $this->getSelect()->joinLeft(
            array('supply_config'=> $this->getTable('magedoc/retailer_config_supply')),
            'main_table.retailer_id = supply_config.retailer_id',
            array(
                'delivery_type'=>'IFNULL(delivery_type, 0)',
                'delivery_term_days'=>'IFNULL(delivery_term_days, 0)',
                'max_order_time'=> $maxOrderTimeExpr
            )
        );
        $this->addFilterToMap('max_order_time', $maxOrderTimeExpr);

        return $this;
    }
}

<?php

class Testimonial_MageDoc_Model_Mysql4_Retailer extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_serializableFields = array(
        'discount_table' => array(array(), array()),
        'margin_table' => array(array(), array()),
    );

    protected function _construct()
    {
        $this->_init('magedoc/retailer', 'retailer_id');
    }

    protected function _updateRetailerLastImportDate( $retailerId )
    {

        $adapter = $this->_getWriteAdapter();

        /** Update retailer last import date */
        return $adapter->update($this->getTable('magedoc/retailer'),  array('last_import_date' => new Zend_Db_Expr('NOW()')),
            array( "retailer_id = {$this->getId()}" )
        )->rowCount();
    }

}



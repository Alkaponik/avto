<?php

class Testimonial_MageDoc_Model_Import_Update extends Testimonial_MageDoc_Model_Import
{
    /**
     * Entity invalidated indexes.
     *
     * @var Mage_ImportExport_Model_Import_Entity_Abstract
     */
    protected static $_entityInvalidatedIndexes = array (
        'magedoc_product' => array (
            'catalog_product_price',
            'catalog_product_flat',
            'cataloginventory_stock',
        )
    );

    protected function _getEntityAdapter()
    {
        if (!$this->_entityAdapter) {
            $validTypes = Mage_ImportExport_Model_Config::getModels(self::CONFIG_KEY_ENTITIES);

            
            if (isset($validTypes[$this->getEntity()])) {
                try {
                    $this->_entityAdapter = Mage::getModel("magedoc/import_entity_product_update");
                } catch (Exception $e) {
                    Mage::logException($e);
                    Mage::throwException(
                        Mage::helper('importexport')->__('Invalid entity model')
                    );
                }
                if (!($this->_entityAdapter instanceof Mage_ImportExport_Model_Import_Entity_Abstract)) {
                    Mage::throwException(
                        Mage::helper('importexport')->__('Entity adapter object must be an instance of Mage_ImportExport_Model_Import_Entity_Abstract')
                    );
                }
            } else {
                Mage::throwException(Mage::helper('importexport')->__('Invalid entity'));
            }
            // check for entity codes integrity
            if ($this->getEntity() != $this->_entityAdapter->getEntityTypeCode()) {
                Mage::throwException(
                    Mage::helper('importexport')->__('Input entity code is not equal to entity adapter code')
                );
            }
            //print_r($this->getData()); die;
            $this->_entityAdapter->setParameters($this->getData());
        }
        return $this->_entityAdapter;
    }


}


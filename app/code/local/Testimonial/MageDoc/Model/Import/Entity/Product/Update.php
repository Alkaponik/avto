<?php

class Testimonial_MageDoc_Model_Import_Entity_Product_Update extends Testimonial_MageDoc_Model_Import_Entity_Product
{
    protected $_updateMode = true;

    protected $_staticAttributesToUpdate = array(
        'updated_at',
        'retailer_id'
    );

    protected $_staticAttributeValues = array(
        "_attribute_set"            => "Default",
        "_type"                     => "spare",
        "status"                    => "1",
        "visibility"                => "4",
        "is_in_stock"               => "1",
        "manage_stock"              => "1",
        "use_config_manage_stock"   => "1",
    );

    /**
     * remove all type model
     * overrided spare type model
     */
    protected function _initTypeModels()
    {
        $config = Mage::getConfig()->getNode(self::CONFIG_KEY_PRODUCT_TYPES)->asCanonicalArray();
        $config = array(
            'simple'    => $config['simple'],
        );
        $config['spare'] = 'magedoc/import_entity_product_type_spare_update';
        foreach ($config as $type => $typeModel) {
            if (!($model = Mage::getModel($typeModel, array($this, $type)))) {
                Mage::throwException("Entity type model '{$typeModel}' is not found");
            }
            if (! $model instanceof Mage_ImportExport_Model_Import_Entity_Product_Type_Abstract) {
                Mage::throwException(
                    Mage::helper('importexport')->__('Entity type model must be an instance of Mage_ImportExport_Model_Import_Entity_Product_Type_Abstract')
                );
            }
            if ($model->isSuitable()) {
                $this->_productTypeModels[$type] = $model;
            }
            $this->_particularAttributes = array_merge(
                $this->_particularAttributes,
                $model->getParticularAttributes()
            );
        }
        // remove doubles
        $this->_particularAttributes = array_unique($this->_particularAttributes);

        return $this;
    }

    protected function _importData()
    {
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->_deleteProducts();
        } else {
            $this->_saveValidatedBunches();
            $this->_saveProducts();
            $this->_saveStockItem();
            foreach ($this->_productTypeModels as $productType => $productTypeModel) {
                $productTypeModel->saveData();
            }
        }
        return true;
    }
    
}

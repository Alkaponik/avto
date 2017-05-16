<?php
abstract class Testimonial_MageDoc_Model_Directory_Abstract extends Mage_Core_Model_Abstract
{
    const ALLOW_CUSTOM_MANUFACTURERS = false;

    protected $_canSuggestProducts = true;
    protected $_priceImportModel;
    protected $_priceUpdateModel;
    protected $_directoryConfigBase;
    protected $_defaultPriceImportModelName = 'magedoc/import';
    protected $_defaultPriceUpdateModelName = 'magedoc/import_update';

    protected function _construct()
    {
        $this->_init('magedoc/directory_abstract');
        $this->_directoryConfigBase =  Testimonial_MageDoc_Model_Directory::DIRECTORIES_CONFIG_XML_PATH . '/'
            . static::CODE ;
    }

    public function joinDirectorySuppliers( $collection, $suggest = false )
    {
        $this->getResource()->joinDirectorySuppliers($collection, $suggest);

        return $this;
    }

    public function linkOffersToDirectory( $source )
    {
        $offersTable = 'magedoc/import_retailer_data_preview';
        $linkTable = 'magedoc/directory_offer_link_preview';

        $this->getResource()->linkOffersToDirectory($offersTable, $linkTable, $source->getId());

        return $this;
    }

    public function getSupplierOptions()
    {
        return $this->getResource()->getSupplierOptions();
    }

    public function getProductOptions($vendorId = null)
    {
        $products = array();
        $collection = $this->getProductCollection($vendorId);
        if ($collection instanceof Varien_Data_Collection_Db) {
            while ($product = $collection->fetchItem()) {
                $products[$product->getId()] = $product->getName();
                if ($product->getCode()) {
                    $products[$product->getId()] .= " ({$product->getCode()})";
                }
            }
        } else {
            foreach  ($collection as $product) {
                $products[$product->getId()] = $product->getName();
                if ($product->getCode()) {
                    $products[$product->getId()] .= " ({$product->getCode()})";
                }
            }
        }
        return $products;
    }

    public function getProductCollection($vendorId = null)
    {
        return $this->getResource()->getProductCollection($vendorId);
    }

    public function joinSuppliers ( $select, $fields = '' )
    {
        $this->getResource()->joinSuppliers( $select, $fields );
    }

    public function getExtraFields()
    {
        $extraFields = $this->getData('extra_fields');

        if(is_array($extraFields)) {
            return $extraFields;
        }

        if( !empty($extraFields) && is_string($extraFields)) {
            $extraFields = explode(',', $extraFields);
            foreach($extraFields as &$field) {
                $field = trim($field);
            }
        } else {
            $extraFields = array();
        }

        $this->setData('extra_fields', $extraFields);

        return $extraFields;
    }

    public function isCustomManufacturersAllowed()
    {
        return static::ALLOW_CUSTOM_MANUFACTURERS;
    }

    public function joinDirectorySuppliersSuggestions( $collection )
    {
        return $this->getResource()->joinDirectorySuppliersSuggestions( $collection );
    }

    public function joinDirectoryProductsSuggestions( $collection )
    {
        return $this->getResource()->joinDirectoryProductsSuggestions( $collection );
    }

    public function updateSupplierIdInSupplierMap($retailerId = null, $manufacturerList = null)
    {
        $this->getResource()->updateSupplierIdInSupplierMap( $this->getCode(), $retailerId,  $manufacturerList );
        return $this;
    }

    public function getPriceImportModel()
    {
        if (!isset($this->_priceImportModel)){
            $this->_priceImportModel = Mage::getModel($this->getPriceImportModelName());
        }
        return $this->_priceImportModel;
    }

    public function getPriceUpdateModel()
    {
        if (!isset($this->_priceUpdateModel)){
            $this->_priceUpdateModel = Mage::getModel($this->getPriceUpdateModelName());
        }
        return $this->_priceUpdateModel;
    }

    public function getPriceImportModelName()
    {
        return $this->getData('price_import_model')
            ? $this->getData('price_import_model')
            : $this->_defaultPriceImportModelName;
    }

    public function getPriceUpdateModelName()
    {
        return $this->getData('price_update_model')
            ? $this->getData('price_update_model')
            : $this->_defaultPriceUpdateModelName;
    }

    public function getRetailerImportModel($retailerId = 0)
    {
        $modelName = $this->getData('retailer_import_model_'.Mage::helper('magedoc/price')->getRetailerById($retailerId)->getModel());
        if (!$modelName){
            $modelName = Mage::helper('magedoc')->getRetailerImportModel($retailerId);
        }
        return Mage::getModel($modelName)->setDirectory($this);
    }

    public function canSuggestProducts()
    {
        return $this->_canSuggestProducts;
    }
}
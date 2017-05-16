<?php
class MageDoc_DirectoryCatalog_Model_Directory extends Testimonial_MageDoc_Model_Directory_Abstract
{
    const CODE = 'catalog';
    const ALLOW_CUSTOM_MANUFACTURERS = true;

    protected $_supplierOptions;
    protected $_normalizedSupplierOptionValues;
    protected $_suppliersBlacklist = array(
        //'sata' => 1, 
        //'120gb' => 1
        );

    protected function _construct()
    {
        $this->_init('directory_catalog/directory');
    }

    public function &getSupplierOptions()
    {
        if (!isset($this->_supplierOptions)){
            $this->_supplierOptions = array();
            $this->_normalizedSupplierOptionValues = array();
            $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer');
            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);

                foreach ($options as $option) {
                    $this->_supplierOptions[$option['value']] = $option['label'];
                    $normalizedLabel = mb_strtolower($option['label'], 'UTF-8');
                    if (!isset($this->_suppliersBlacklist[$normalizedLabel])){
                        $this->_normalizedSupplierOptionValues[$normalizedLabel] = $option['value'];
                    }
                }
            }
        }

        return $this->_supplierOptions;
    }

    public function &getNormalizedSupplierOptionValues()
    {
        if (!isset($this->_normalizedSupplierOptionValues)) {
            $this->getSupplierOptions();
        }
        return $this->_normalizedSupplierOptionValues;
    }

    public function addSupplier( $manufacturer )
    {
        $supplierId = array_search( $manufacturer , $this->getSupplierOptions());
        if( $supplierId !== false ) {
            return $supplierId;
        }

        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer');

        $attribute->setOption(
            array(
                 'value' => array(
                     "store_0" => array(
                         0 => $manufacturer,
                     )
                 )
            )
        );
        try {
            $attribute->save();
            return array_search( $manufacturer , $this->getSupplierOptions());

        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function updateDirectoryOfferLink()
    {
        return $this->getResource()->updateDirectoryOfferLink();
    }
}
<?php

class Testimonial_MageDoc_Model_Mysql4_Model extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_isPkAutoIncrement = false;
        $this->_init('magedoc/model', 'td_mod_id');
    }

    public function deleteProductByType($modelId)
    {
        $typeProductTable = $this->getTable('magedoc/type_product');
        $typeTable = Mage::getResourceModel('magedoc/tecdoc_type')->getMainTable();

        $sql = 'DELETE ' . $typeProductTable . ' FROM ' . $typeProductTable
            .' INNER JOIN ' . $typeTable . ' ON TYP_ID = ' . $typeProductTable .'.type_id'
            .' AND TYP_MOD_ID = ?';
        $this->_getWriteAdapter()->query($sql,$modelId);
    }

    public function setTypeProduct($productIds, $modelId)
    {
        $typeCollection = Mage::getResourceModel('magedoc/tecdoc_type_collection')->addModelFilter($modelId);
        $typeIds = $typeCollection->getColumnValues('typ_id');
        $typeProductTable = $this->getTable('magedoc/type_product');
        $data = $this->_prepareValuesToInsert($productIds, $typeIds);

        $this->_getWriteAdapter()->insertMultiple($typeProductTable, $data);
    }

    protected function _prepareValuesToInsert($productIds, $typeIds)
    {
        $result = array();
        foreach($typeIds as $typeId){
            foreach($productIds as $productId){
                $result[] = array(
                    'product_id' => (int)$productId,
                    'type_id' => (int)$typeId,
                    'type' => 'U'
                );
            }
        }
        return $result;
    }
}


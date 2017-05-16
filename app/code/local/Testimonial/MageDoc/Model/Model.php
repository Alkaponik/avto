<?php

class Testimonial_MageDoc_Model_Model extends Mage_Core_Model_Abstract
{
    const ENTITY = 'magedoc_model';

    protected $_models = array();

    protected function _construct()
    {
        $this->_init('magedoc/model');
    }

    public function getUrl($useSid = null)
    {
        return $this->getUrlModel()->getModelUrl($this, $useSid);
    }

    /**
     * Get product url model
     *
     * @return Testimonial_MageDoc_Model_Url
     */
    public function getUrlModel()
    {
        if ($this->_urlModel === null) {
            $this->_urlModel = Mage::getSingleton('magedoc/url');
        }
        return $this->_urlModel;
    }

    /**
     * Init index
     *
     * @return Testimonial_MageDoc_Model_Manufacturer
     */
    protected function _afterSave()
    {
        $result = parent::_afterSave();

        Mage::getSingleton('index/indexer')->processEntityAction(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
        return $result;
    }

    public function getSelectedProducts()
    {
        $collection = Mage::getResourceModel('magedoc/tecdoc_type_collection')
            ->addModelFilter($this->getId());

        $typeProductTable = Mage::getSingleton('core/resource')->getTableName('magedoc/type_product');

        $collection->getSelect()->joinInner(
            $typeProductTable,
            'typ_id = type_id',
            array(
                'product_id'        => 'product_id',
                'product_type_id'   => new Zend_Db_Expr('CONCAT(product_id, \'_\', type_id)'),
                )
        );
        $collection->setIdFieldName('product_type_id');

        return $collection->getColumnValues('product_id');
    }

    public function deleteTypeProduct()
    {
        $this->getResource()->deleteProductByType($this->getId());
        return $this;
    }

    public function setTypeProduct($ids)
    {
        $this->getResource()->setTypeProduct($ids, $this->getId());
        return $this;
    }

    public function factory($modelId)
    {
        if (!isset($this->_models[$modelId])){
            $model = Mage::getModel('magedoc/model')->load($modelId);
            $this->_models[$modelId] = $model;
        }
        return $this->_models[$modelId];
    }

    public function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }
}


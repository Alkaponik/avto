<?php

class Testimonial_FlatCatalog_Model_Product extends Mage_Catalog_Model_Product
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY                 = 'flatcatalog_product';

    const CACHE_TAG              = 'flatcatalog_product';
    protected $_cacheTag         = 'flatcatalog_product';
    protected $_eventPrefix      = 'flatcatalog_product';
    protected $_eventObject      = 'rxproduct';

    protected $_calculatePrice = false;

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('flatcatalog/product');
    }

    /**
     * Retrieve type instance
     *
     * Type instance implement type depended logic
     *
     * @param  bool $singleton
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    public function getTypeInstance($singleton = false)
    {
        if ($singleton === true) {
            if (is_null($this->_typeInstanceSingleton)) {
                $this->_typeInstanceSingleton = Mage::getSingleton('catalog/product_type')
                    ->factory($this, true);
            }
            return $this->_typeInstanceSingleton;
        }

        if ($this->_typeInstance === null) {
            $this->_typeInstance = Mage::getSingleton('catalog/product_type')
                ->factory($this);
        }
        return $this->_typeInstance;
    }

    /**
     * Get product url model
     *
     * @return Mage_Catalog_Model_Product_Url
     */
    public function getUrlModel()
    {
        if ($this->_urlModel === null) {
            $this->_urlModel = Mage::getSingleton('flatcatalog/product_url');
        }
        return $this->_urlModel;
    }

    /**
     * Clear cache related with product id
     *
     * @return Mage_Catalog_Model_Product
     */
    public function cleanCache()
    {
        Mage::app()->cleanCache('flatcatalog_product_'.$this->getId());
        return $this;
    }

    public function getResourceCollection()
    {
        return Mage_Core_Model_Abstract::getResourceCollection();
    }

    public function getAttributeText($attributeCode)
    {
        return $this->getDataUsingMethod($attributeCode);
    }

    public function getIsCoolingProduct()
    {
        return $this->getIsCooled();
    }

    public function getContentsValue()
    {
        return $this->getData('contents_value');
    }

    public function getContentsEntity($packageType = null)
    {
        $contentsEntity = Mage::helper('flatcatalog')->getContentsEntity($this->getData('content_package_type'));
        return $this->getData('contents_entity') . ($contentsEntity ? ' ' . $contentsEntity : '') ;
    }

    public function updateFinalPrice()
    {
        /** @var Testimonial_MageDoc_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('magedoc/price');

        /** @var $collection Testimonial_MageDoc_Model_Mysql4_Retailer_Collection */
        $collection = $this->getCollection();
        $collection->setItemObjectClass('Varoen_Object');
        /** @var Testimonial_FlatCatalog_Model_Product $item */

        $bunch = array();
        $bunchSize = 0;
        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns(array('data_id', 'retailer_id', 'cost', 'price', 'qty'));
        $result = $collection->getConnection()->query($select);
        $item = new Varien_Object();
        while($itemData = $result->fetch()) {
            $item->setData($itemData);
            $finalPrice = $priceHelper->getFinalPrice($item);

            $bunch[] = array(
                'data_id' => $item->getDataId(),
                'final_price' => $finalPrice,
            );

            $bunchSize++;
            if($bunchSize >= 10000) {
                $this->getResource()->updateFinalPriceFromBunch( $bunch );
                $bunch = array();
                $bunchSize = 0;
            }
        }

        $this->getResource()->updateFinalPriceFromBunch( $bunch );

        return $this;
    }
}

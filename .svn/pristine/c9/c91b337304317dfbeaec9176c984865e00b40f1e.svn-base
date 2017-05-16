<?php
class Testimonial_Avtoto_Model_Price extends Mage_Core_Model_Abstract
{
    const XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';
    const AVTOTO_STORE_NAME = 'avtoto';

    protected function _construct()
    {
        $this->_init('avtoto/price');
    }

    public function updateShopPrice()
    {
        $this->updateShopRetailersTable();
        $this->updateShopPriceTable();

        return $this;
    }

    public function updateShopRetailersTable()
    {
        /** @var Testimonial_Avtoto_Model_Resource_Price $resource */
        $resource = $this->getResource();
        $resource->updateShopRetailersTable();

        return $this;
    }

    public function updateShopPriceTable()
    {
        /** @var Testimonial_Avtoto_Model_Resource_Price $resource */
        $resource = $this->getResource();
        $resource->updateShopPriceTable();

        return $this;
    }

    public function updateCatalog()
    {
        Mage::register('magedoc_directory', Mage::getSingleton('magedoc/directory')->getDirectory());
        Mage::getModel('magedoc/import_update')->importSource();
        /** @var Testimonial_Avtoto_Model_Resource_Price $resource */
        $resource = $this->getResource();
        $resource->updateCatalogFromShop();

        return $this;
    }

    public function updateShopCatalog()
    {
        $baseUrl = Mage::getStoreConfig(static::XML_PATH_UNSECURE_BASE_URL, static::AVTOTO_STORE_NAME);
        $url = "{$baseUrl}move/_import_price.php?retail=0";
        Mage::app()->getFrontController()->getAction()->getResponse()->setRedirect($url);

        return $this;
    }

    public function updateShopPrices()
    {
        //Mage::register('magedoc_directory', Mage::getSingleton('magedoc/directory')->getDirectory());
        //Mage::getModel('magedoc/import_update')->importSource();
        /** @var Testimonial_Avtoto_Model_Resource_Price $resource */
        $resource = $this->getResource();
        $resource->updateShopPrices();

        return $this;
    }
}
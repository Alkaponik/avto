<?php

class MageDoc_DirectoryCatalog_Model_Observer
{
    public function catalog_product_save_after(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        $productIndex = Mage::getModel('directory_catalog/product_index')->load($product->getId());
        $productIndex->setProductId($product->getId());
        $productIndex->setCodeNormalized(Mage::helper('magedoc')->normalizeCode($product->getCode()));
        $productIndex->setModelNormalized(Mage::helper('magedoc')->normalizeCode($product->getModel()));
        $productIndex->setCodeModelNormalized($productIndex->getCodeNormalized().$productIndex->getModelNormalized());
        $productIndex->save();
        return $this;
    }
}
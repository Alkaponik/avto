<?php

class MageDoc_OrderExport_Model_Observer
{
    public function sales_quote_item_set_product(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        $quoteItem = $observer->getQuoteItem();
        $quoteItem->setUtbIsbnUtb($product->getUtbIsbnUtb());
        $quoteItem->setUtbType($product->getUtbType());
    }
}
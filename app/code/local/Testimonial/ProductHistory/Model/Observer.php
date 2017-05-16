<?php
    class Testimonial_ProductHistory_Model_Observer {

	public function catalog_product_save_after($observer)
	{
		$history = Mage::getModel('producthistory/history');
		$product = $observer->getEvent()->getProduct();
		
		if($product->getData('is_enabled_history'))
		{
			if($product->getOrigData('price') != $product->getData('price'))
			{
				$history->setProductId($product->getId());
				$history->setPrice($product->getPrice());
				$history->setStoreId($product->getStoreId());
				$history->save();
			}
		}
	}

	public function adminhtml_block_html_before($observer)
	{
        	$block = $observer->getEvent()->getBlock();

        	if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes)
		{
			
      			if((Mage::app()->getRequest()->getActionName() == 'edit') && ($block->getGroup()->getAttributeGroupName() == 'Product history'))
			{
				$block->getForm()->getFieldsetRenderer()->setTemplate('producthistory/catalog/product/tab/ProductHistory.phtml');
            		}
        	}
    	}


    }

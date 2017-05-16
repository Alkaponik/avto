<?php
class Phoenix_Brands_Block_Catalog_Category_Brand_View extends Mage_Catalog_Block_Category_View
{    
    public function getCollectionListHtml()
    {
        return $this->getChildHtml('collection_list');
    }
    
    public function getReviewedProductListHtml()
    {
        return $this->getChildHtml('reviewed_product_list');
    }

    public function getRatingBlockHtml()
    {
        return $this->getChildHtml('rating');
    }
    
}

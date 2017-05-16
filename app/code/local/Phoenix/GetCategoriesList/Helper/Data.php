<?php
/**
 * Sample Widget Helper
 */
class Phoenix_GetCategoriesList_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSavingAmount($product)
    {
        if ($this->showUvpPrice() == 1){
            $product_final_price = Mage::helper('tax')->getPrice($product, $product->getFinalPrice());
            if (!$product_final_price) {
                return false;
            }

            if (!$product->getCost() || $product->getCost()/$product_final_price < 1.1) {

                return false;
            }

            return $product->getCost() - $product_final_price;
        }
       return false;
    }

    public function showUvpPrice()
    {
        return (int)Mage::getStoreConfig('rabbits/general/show_uvp');
    }
    
    public function showSavingAmount(){
        return (int)Mage::getStoreConfig('rabbits/general/saving_amount');
    }
}
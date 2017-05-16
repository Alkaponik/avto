<?php

class Testimonial_MageDoc_Model_Quote_Item extends Mage_Sales_Model_Quote_Item
{
    /**
     * Check product representation in item
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  bool
     */
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if (!$product || $itemProduct->getId() != $product->getId()
            || $itemProduct->getRetailerId() != $product->getRetailerId()) {
            return false;
        }

        /**
         * Check maybe product is planned to be a child of some quote item - in this case we limit search
         * only within same parent item
         */
        $stickWithinParent = $product->getStickWithinParent();
        if ($stickWithinParent) {
            if ($this->getParentItem() !== $stickWithinParent) {
                return false;
            }
        }

        // Check options
        $itemOptions    = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if(!$this->compareOptions($itemOptions, $productOptions)){
            return false;
        }
        if(!$this->compareOptions($productOptions, $itemOptions)){
            return false;
        }
        return true;
    }

    /**
     * Setup product for quote item
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function setProduct($product)
    {
        if ($this->getQuote()) {
            $product->setStoreId($this->getQuote()->getStoreId());
            $product->setCustomerGroupId($this->getQuote()->getCustomerGroupId());
        }
        $this->setData('product', $product)
            ->setProductId($product->getId())
            ->setProductType($product->getTypeId())
            ->setSku($this->getProduct()->getSku())
            ->setName($product->getName())
            ->setWeight($this->getProduct()->getWeight())
            ->setTaxClassId($product->getTaxClassId())
            ->setIsRecurring($product->getIsRecurring())
        ;
        if (!$this->hasBaseCost()){
            $this->setBaseCost($product->getCost());
        }

        if ($product->getStockItem()) {
            $this->setIsQtyDecimal($product->getStockItem()->getIsQtyDecimal());
        }

        Mage::dispatchEvent('sales_quote_item_set_product', array(
            'product' => $product,
            'quote_item'=>$this
        ));


//        if ($options = $product->getCustomOptions()) {
//            foreach ($options as $option) {
//                $this->addOption($option);
//            }
//        }
        return $this;
    }

    /**
     * Get item cost used for quote calculation process.
     * This method get custom cost (if it is defined) or original product final cost
     *
     * @return float
     */
    public function getCalculationCost()
    {
        $cost = $this->_getData('calculation_cost');
        if (is_null($cost)) {
            if ($this->hasCustomCost()) {
                $cost = $this->getCustomCost();
            } else {
                $cost = $this->getConvertedCost();
            }
            $this->setData('calculation_cost', $cost);
        }
        return $cost;
    }

    /**
     * Get item price used for quote calculation process.
     * This method get original custom price applied before tax calculation
     *
     * @return float
     */
    public function getCalculationCostOriginal()
    {
        $cost = $this->_getData('calculation_cost');
        if (is_null($cost)) {
            if ($this->hasOriginalCustomCost()) {
                $cost = $this->getOriginalCustomCost();
            } else {
                $cost = $this->getConvertedCost();
            }
            $this->setData('calculation_cost', $cost);
        }
        return $cost;
    }

    /**
     * Get calculation cost used for quote calculation in base currency.
     *
     * @return float
     */
    public function getBaseCalculationCost()
    {
        if (!$this->hasBaseCalculationCost()) {
            if ($this->hasCustomCost()) {
                $cost = (float) $this->getCustomCost();
                if ($cost) {
                    $rate = $this->getStore()->convertPrice($cost) / $cost;
                    $cost = $cost / $rate;
                }
            } else {
                $cost = $this->getCost();
            }
            $this->setBaseCalculationCost($cost);
        }
        return $this->_getData('base_calculation_cost');
    }

    /**
     * Get original calculation cost used for quote calculation in base currency.
     *
     * @return float
     */
    public function getBaseCalculationCostOriginal()
    {
        if (!$this->hasBaseCalculationCost()) {
            if ($this->hasOriginalCustomCost()) {
                $cost = (float) $this->getOriginalCustomCost();
                if ($cost) {
                    $rate = $this->getStore()->convertPrice($cost) / $cost;
                    $cost = $cost / $rate;
                }
            } else {
                $cost = $this->getCost();
            }
            $this->setBaseCalculationCost($cost);
        }
        return $this->_getData('base_calculation_cost');
    }

    /**
     * Get original cost (retrieved from product) for item.
     * Original cost value is in quote selected currency
     *
     * @return float
     */
    public function getOriginalCost()
    {
        $cost = $this->_getData('original_cost');
        if (is_null($cost)) {
            $cost = $this->getStore()->convertPrice($this->getBaseOriginalCost());
            $this->setData('original_cost', $cost);
        }
        return $cost;
    }

    /**
     * Set original cost to item (calculation price will be refreshed too)
     *
     * @param   float $price
     * @return  Mage_Sales_Model_Quote_Item_Abstract
     */
    public function setOriginalCost($cost)
    {
        return $this->setData('original_cost', $cost);
    }

    /**
     * Get Original item cost (got from product) in base website currency
     *
     * @return float
     */
    public function getBaseOriginalCost()
    {
        return $this->_getData('base_original_cost');
    }

    /**
     * Specify custom item price (used in case whe we have apply not product price to item)
     *
     * @param   float $value
     * @return  Mage_Sales_Model_Quote_Item_Abstract
     */
    public function setCustomCost($value)
    {
        $this->setCalculationCost($value);
        $this->setBaseCalculationCost(null);
        return $this->setData('custom_cost', $value);
    }

    /**
     * Get item price. Item price currency is website base currency.
     *
     * @return decimal
     */
    public function getCost()
    {
        return $this->_getData('cost');
    }

    /**
     * Specify item price (base calculation price and converted price will be refreshed too)
     *
     * @param   float $value
     * @return  Mage_Sales_Model_Quote_Item_Abstract
     */
    public function setCost($value)
    {
        $this->setBaseCalculationCost(null);
        $this->setConvertedCost(null);
        return $this->setData('cost', $value);
    }

    /**
     * Get item price converted to quote currency
     * @return float
     */
    public function getConvertedCost()
    {
        $cost = $this->_getData('converted_cost');
        if (is_null($cost)) {
            $cost = $this->getStore()->convertPrice($this->getCost());
            $this->setData('converted_cost', $cost);
        }
        return $cost;
    }

    /**
     * Set new value for converted cost
     * @param float $value
     * @return Mage_Sales_Model_Quote_Item_Abstract
     */
    public function setConvertedCost($value)
    {
        $this->setCalculationPrice(null);
        $this->setData('converted_cost', $value);
        return $this;
    }
}

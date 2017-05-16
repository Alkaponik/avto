<?php

class Phoenix_ProductDiscount_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * get max value of saving in Euro (show discount in EUR)
     * @return int
     */
    public function getEuroLimitDiscount(){
        return (float)Mage::getStoreConfig('productdiscount/settings/discount_euro_limit');
    }

    /**
     * get max value of saving in percent (show big or small picture)
     *
     * @return int
     */
    public function getPercentLimitDiscount(){
        return (float)Mage::getStoreConfig('productdiscount/settings/discount_percent_limit');
    }

    /**
     * return value of Cost attribute
     */
    public function getCost($_product){
        if ($_product->getCost() > 0){
            return Mage::helper('core')->currency($_product->getCost(), true, false);
        }
        return false;
    }

    /**
     * get saving value in EURO
     */
    public function getDiscountValue($_product) {
        if ($_product->getCost() > 0){
            //return Mage::helper('core')->currency($_product->getCost() - $this->getPrice($_product), true, false) ;
            return $_product->getCost() - $this->getPrice($_product);
        }
        return false;
    }

    /**
     * get saving value in percent
     */
    public function getDiscountPercent($_product) {
        if ($_product->getCost() > 0){
            return round(100 - (($this->getPrice($_product) * 100) / $_product->getCost()), 0);
        }
        return false;
    }

    public function getPrice($_product) {
        
            $simplePricesTax = (Mage::helper('tax')->displayPriceIncludingTax() || Mage::helper('tax')->displayBothPrices());

            $_weeeTaxAmount = Mage::helper('weee')->getAmountForDisplay($_product);
            if (Mage::helper('weee')->typeOfDisplay($_product, array(1,2,4))) {
                $_weeeTaxAmount = Mage::helper('weee')->getAmount($_product);
                $_weeeTaxAttributes = Mage::helper('weee')->getProductWeeeAttributesForDisplay($_product);
            }

            $_price = Mage::helper('tax')->getPrice($_product, $_product->getPrice());
            $_regularPrice = Mage::helper('tax')->getPrice($_product, $_product->getPrice(), $simplePricesTax) ;
            $_finalPrice = Mage::helper('tax')->getPrice($_product, $_product->getFinalPrice()) ;
            $_finalPriceInclTax = Mage::helper('tax')->getPrice($_product, $_product->getFinalPrice(), true);

            $resultPrice=$_price;

            if ($_finalPrice == $_price) {
                if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 0)) { // including
                    $resultPrice = $_price+$_weeeTaxAmount;
                } elseif ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 1)) { // incl. + weee
                    $resultPrice = $_price+$_weeeTaxAmount;
                } elseif ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 4)) { // incl. + weee
                    $resultPrice = $_price+$_weeeTaxAmount ;
                } elseif ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 2)) { // excl. + weee + final
                    $resultPrice = $_price+$_weeeTaxAmounte ;
                } else {
                    $resultPrice =  $_price;
                }
            } else { /* if ($_finalPrice == $_price){ */
                $_originalWeeeTaxAmount = Mage::helper('weee')->getOriginalAmount($_product);
                if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 0)) { // including
                    $resultPrice =  $_finalPrice+$_weeeTaxAmount ;
                } elseif ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 1)) { // incl. + weee
                    $resultPrice =  $_finalPrice+$_weeeTaxAmount ;
                } elseif ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 4)) { // incl. + weee
                    $resultPrice =  $_finalPrice+$_originalWeeeTaxAmount ;
                } elseif ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 2)) { // excl. + weee + final
                    $resultPrice = $_finalPrice;
                } else { // excl.
                    $resultPrice =  $_finalPriceInclTax;
                }
            } /* if ($_finalPrice == $_price){ */
        return $resultPrice;
    }//function getPrice()


}
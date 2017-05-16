<?php

class Testimonial_MageDoc_Model_Quote_Address_Total_Cost extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('cost');
    }

    /**
     * Collect total cost of quote items
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Testimonial_MageDoc_Model_Quote_Address_Total_Cost
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_setAddress($address);
        $baseCost = 0;
        $cost = 0;

        foreach ($this->_getAddressItems($address) as $item) {
            if (!$item->getHasChildren()){
                $baseAmount = $item->getBaseCalculationCost()*$item->getQty();
                $baseCost += $baseAmount;
                $cost += $address->getQuote()->getStore()->convertPrice($baseAmount, false);
                $item->setBaseOriginalCost($item->getBaseCost());
            }
        }
        $address->setBaseCost($baseCost);
        $address->setCost($cost);

        return $this;
    }

    protected function _getAddressItems(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        if (!$quote instanceof Testimonial_MageDoc_Model_Quote
            || $address->getAddressType() != Mage_Sales_Model_Quote_Address::TYPE_SHIPPING){
            return $address->getAllNonNominalItems();
            //return parent::_getAddressItems($address);
        }
        return array_merge($address->getAllNonNominalItems(), $quote->getAllInquiries());
    }

    /**
     * Add shipping totals information to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getCost();
        if (Mage::app()->getStore()->isAdmin() && $amount != 0) {
            $title = Mage::helper('magedoc')->__('Cost');

            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }
        return $this;
    }

    /**
     * Get Cost label
     *
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('magedoc')->__('Cost');
    }
}

<?php

class Testimonial_MageDoc_Model_Quote_Address_Total_Margin extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('margin');
    }

    /**
     * Collect total cost of quote items
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Testimonial_MageDoc_Model_Quote_Address_Total_Margin
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_setAddress($address);
        $baseMargin = $address->getBaseSubtotalInclTax() - $address->getBaseCost();
        $margin = $address->getSubtotalInclTax() - $address->getCost();

        $address->setBaseMargin($baseMargin);
        $address->setMargin($margin);

        return $this;
    }

    /**
     * Add shipping totals information to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getMargin();
        if (Mage::app()->getStore()->isAdmin() && $amount != 0) {
            $title = Mage::helper('magedoc')->__('Margin');

            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }
        return $this;
    }

    /**
     * Get Margin label
     *
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('magedoc')->__('Margin');
    }
}

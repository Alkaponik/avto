<?php

class Testimonial_MageDoc_Model_Order_Invoice_Total_Cost extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect total cost of invoiced items
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Mage_Sales_Model_Order_Invoice_Total_Cost
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        if(!$invoice instanceof Testimonial_MageDoc_Model_Order_Invoice){
            return $this;
        }
        $baseInvoiceTotalCost = 0;
        $invoiceTotalCost = 0;
        foreach ($invoice->getAllInquiries() as $inquiry) {
            if (!$inquiry->getHasChildren()){
                $baseCost = $inquiry->getBaseCost()*$inquiry->getQty();
                $baseInvoiceTotalCost += $baseCost;
                $invoiceTotalCost += $invoice->getStore()->convertPrice($baseCost, false);
            }
        }

        foreach ($invoice->getAllItems() as $item) {
            if (!$item->getHasChildren()){
                $baseCost = $item->getBaseCost()*$item->getQty();
                $baseInvoiceTotalCost += $baseCost;
                $invoiceTotalCost += $invoice->getStore()->convertPrice($baseCost, false);
            }
        }
        $invoice->setBaseCost($baseInvoiceTotalCost);
        $invoice->setCost($invoiceTotalCost);
        return $this;
    }
}

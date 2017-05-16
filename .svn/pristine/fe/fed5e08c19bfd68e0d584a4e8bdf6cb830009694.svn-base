<?php

class Testimonial_MageDoc_Model_Order_Invoice_Total_Subtotal 
        extends Mage_Sales_Model_Order_Invoice_Total_Subtotal
{
    /**
     * Collect invoice subtotal
     *
     * @param   Mage_Sales_Model_Order_Invoice $invoice
     * @return  Mage_Sales_Model_Order_Invoice_Total_Subtotal
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        if(!$invoice instanceof Testimonial_MageDoc_Model_Order_Invoice){
            return parent::collect($invoice);
        }
        $subtotal       = 0;
        $baseSubtotal   = 0;
        $subtotalInclTax= 0;
        $baseSubtotalInclTax = 0;

        $order = $invoice->getOrder();

        foreach ($invoice->getAllItemsAndInquiries() as $item) {
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }

            $item->calcRowTotal();

            $subtotal       += $item->getRowTotal();
            $baseSubtotal   += $item->getBaseRowTotal();
            $subtotalInclTax+= $item->getRowTotalInclTax();
            $baseSubtotalInclTax += $item->getBaseRowTotalInclTax();
        }

        $allowedSubtotal = $order->getSubtotal() - $order->getSubtotalInvoiced();
        $baseAllowedSubtotal = $order->getBaseSubtotal() - $order->getBaseSubtotalInvoiced();
        $allowedSubtotalInclTax = $allowedSubtotal + $order->getHiddenTaxAmount()
            + $order->getTaxAmount() - $order->getTaxInvoiced() - $order->getHiddenTaxInvoiced();
        $baseAllowedSubtotalInclTax = $baseAllowedSubtotal + $order->getBaseHiddenTaxAmount()
            + $order->getBaseTaxAmount() - $order->getBaseTaxInvoiced() - $order->getBaseHiddenTaxInvoiced();

        /**
         * Check if shipping tax calculation is included to current invoice.
         */
        $includeShippingTax = true;
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
            if ($previousInvoice->getShippingAmount() && !$previousInvoice->isCanceled()) {
                $includeShippingTax = false;
                break;
            }
        }

        if ($includeShippingTax) {
            $allowedSubtotalInclTax     -= $order->getShippingTaxAmount();
            $baseAllowedSubtotalInclTax -= $order->getBaseShippingTaxAmount();
        } else {
            $allowedSubtotalInclTax     += $order->getShippingHiddenTaxAmount();
            $baseAllowedSubtotalInclTax += $order->getBaseShippingHiddenTaxAmount();
        }

        if ($invoice->isLast()) {
            $subtotal = $allowedSubtotal;
            $baseSubtotal = $baseAllowedSubtotal;
            $subtotalInclTax = $allowedSubtotalInclTax;
            $baseSubtotalInclTax  = $baseAllowedSubtotalInclTax;
        } else {
            $subtotal = min($allowedSubtotal, $subtotal);
            $baseSubtotal = min($baseAllowedSubtotal, $baseSubtotal);
            $subtotalInclTax = min($allowedSubtotalInclTax, $subtotalInclTax);
            $baseSubtotalInclTax = min($baseAllowedSubtotalInclTax, $baseSubtotalInclTax);
        }

        $invoice->setSubtotal($subtotal);
        $invoice->setBaseSubtotal($baseSubtotal);
        $invoice->setSubtotalInclTax($subtotalInclTax);
        $invoice->setBaseSubtotalInclTax($baseSubtotalInclTax);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $subtotal);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseSubtotal);
        return $this;
    }
}

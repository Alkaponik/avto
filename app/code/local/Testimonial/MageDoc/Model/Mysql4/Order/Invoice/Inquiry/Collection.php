<?php

class Testimonial_MageDoc_Model_Mysql4_Order_Invoice_Inquiry_Collection 
        extends Mage_Sales_Model_Resource_Order_Invoice_Item_Collection
{

    protected function _construct()
    {
        $this->_init('magedoc/order_invoice_inquiry');
    }

    public function setInvoiceFilter($invoice)
    {
        if ($invoice instanceof Testimonial_MageDoc_Model_Order_Invoice) {
            $invoiceId = $invoice->getId();
            if ($invoiceId) {
                $this->addFieldToFilter('parent_id', $invoiceId);
            }else{ 
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter('parent_id', $invoice);
        }
        return $this;
    }
}

<?php
class Testimonial_MageDoc_Block_Adminhtml_Order_Invoice_Create_Items extends Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Items
{
    /**
     * Prepare child blocks
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Items
     */
    protected function _beforeToHtml()
    {
        $onclick = "submitAndReloadArea($('invoice_item_container'),'".$this->getUpdateUrl()."')";
        $this->setChild(
            'update_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'class'     => 'update-button',
                'label'     => Mage::helper('sales')->__('Update Qty\'s'),
                'onclick'   => $onclick,
            ))
        );
        $this->_disableSubmitButton = true;
        $_submitButtonClass = ' disabled';
        foreach ($this->getInvoice()->getAllItemsAndInquiries() as $item) {
            /**
             * @see bug #14839
             */
            if ($item->getQty()/* || $this->getSource()->getData('base_grand_total')*/) {
                $this->_disableSubmitButton = false;
                $_submitButtonClass = '';
                break;
            }
        }
        if ($this->getOrder()->getForcedDoShipmentWithInvoice()) {
            $_submitLabel = Mage::helper('sales')->__('Submit Invoice and Shipment');
        } else {
            $_submitLabel = Mage::helper('sales')->__('Submit Invoice');
        }
        $this->setChild(
            'submit_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label'     => $_submitLabel,
                'class'     => 'save submit-button' . $_submitButtonClass,
                'onclick'   => 'disableElements(\'submit-button\');$(\'edit_form\').submit()',
                'disabled'  => $this->_disableSubmitButton
            ))
        );

        return parent::_prepareLayout();
    }
}

<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'InvoiceController.php';

class Testimonial_MageDoc_Sales_InvoiceController extends Mage_Adminhtml_Sales_InvoiceController
{
    protected function _setActiveMenu($menuPath)
    {
        parent::_setActiveMenu('magedoc/order');
        return $this;
        
    }
 
}

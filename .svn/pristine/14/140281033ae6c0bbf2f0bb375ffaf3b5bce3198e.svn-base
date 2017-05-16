<?php
class  Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Supplier_Map_Grid
    extends Testimonial_MageDoc_Block_Adminhtml_Supplier_Map_Grid
{
    protected function _prepareCollectionAfter($collection)
    {
        $retailerId = Mage::registry('retailer')->getRetailerId();
        $collection->addFieldToFilter('main_table.retailer_id', $retailerId);
    }

    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->setId('mapItem');
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('supplierMap');
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('retailer_id');

        return $this;
    }

    protected function _isSuggestAction()
    {
        $suggest = $this->getRequest()->getParam('suggest');
        return !empty($suggest);
    }
}
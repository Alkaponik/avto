<?php

class MageDoc_DirectoryCatalog_Model_Import_Default extends MageDoc_DirectoryCatalog_Model_Import_Aggregator
{
    protected function _prepareCollection()
    {
        Testimonial_MageDoc_Model_Import_Abstract::_prepareCollection();

        if($this->getRetailerId()){
            $this->_collection->getSelect()->where("main_table.retailer_id  = {$this->getRetailerId()}");
        }
        $this->_collection->getSelect()
            ->group($this->_getSkuExpr());

        return $this;
    }

    public function getAdditionalData($item)
    {
        return Testimonial_MageDoc_Model_Import_Abstract::getAdditionalData($item);
    }
}
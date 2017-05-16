<?php

class Testimonial_MageDoc_Model_Import_Default extends Testimonial_MageDoc_Model_Import_Abstract
{
    protected function _prepareCollection()
    {       
        parent::_prepareCollection();
        if($this->getRetailerId()){
            $this->_collection->getSelect()->where("main_table.retailer_id  = {$this->getRetailerId()}");
        }    
        $this->_collection->getSelect()->group('art_id');
        return $this;
    }

    
}

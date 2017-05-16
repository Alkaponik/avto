<?php

class Testimonial_MageDoc_Block_CatalogSearch_Layer extends Mage_CatalogSearch_Block_Layer
{

    protected $_typeFilterBlockName;
    
    protected function _initBlocks()
    {
        $this->_typeFilterBlockName = 'magedoc/catalog_layer_filter_type';
        parent::_initBlocks();
    }

    protected function _prepareLayout()
    {
        $typeBlock = $this->getLayout()->createBlock($this->_typeFilterBlockName)
            ->setLayer($this->getLayer())
            ->init();

        $this->setChild('magedoc_type_filter', $typeBlock);
        return parent::_prepareLayout();
    }

    public function getFilters()
    {
        $filters = parent::getFilters();
        if ($typeFilter = $this->_getTypeFilter()) {
            array_unshift($filters, $typeFilter);
        }
        return $filters;
    }

    protected function _getTypeFilter()
    {
        return $this->getChild('magedoc_type_filter');
    }
    
    
    public function canShowOptions()
    {
        foreach ($this->getFilters() as $filter) {
            if ($filter->getItemsCount()) {
                return true;
            }
            if ($filter->getName() == 'Vehicle') {
                return true;
            }
        }

        return false;
    }
}
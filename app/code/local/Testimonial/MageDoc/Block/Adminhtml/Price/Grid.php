<?php

class Testimonial_MageDoc_Block_Adminhtml_Price_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_collection;
    protected $_importModel;
    protected $_rowData = array();
    protected $_massactionBlockName = 'magedoc/adminhtml_price_grid_massaction';
    
    protected $_retailers = array();
    
    public function __construct()
    {
        parent::__construct();

        $this->setSaveParametersInSession(true);
        $this->_defaultFilter = array();
        $this->setDefaultFilterValues();
        $this->setMassactionIdField('art_id');
    }

    public function setDefaultFilterValues()
    {
        if ($supplierOptions = Mage::getSingleton('magedoc/source_supplier')->getOptionArray()) {
            $this->_defaultFilter['supplier'] = key($supplierOptions);
        }
        if ($retailerOptions = Mage::getSingleton('magedoc/source_retailer')->getOptionArray()) {
            $this->_defaultFilter['retailer'] = key($retailerOptions);   
        }
        if ($categoryOptions = Mage::getSingleton('magedoc/adminhtml_system_config_source_category')->getOptionArray()) {
            $this->_defaultFilter['category'] = key($categoryOptions);
        }
        
        return $this;
    }
    
    
    public function setRetailerImportModel($retailerId)
    {
        $retailerId = !$retailerId ? Testimonial_MageDoc_Helper_Price::AGGREGATOR_RETAILER_ID : $retailerId;
        $this->_importModel = $this->_getCurrentDirectory()->getRetailerImportModel($retailerId);
//        $this->_importModel->setRetailerId($retailerId);
        return $this;
    }
    
    
    public function getImportModel()
    {
        if(!isset($this->_importModel)){
            $this->_importModel = Mage::getModel('magedoc/import_default');
        }

        return $this->_importModel;
    }
    
    
    protected function _prepareCollection()
    {
        $condition = $this->getColumn('retailer')->getFilter()->getCondition();
        if (isset($condition['eq'])){
            $retailerId = $condition['eq'];
        } elseif (isset($this->_defaultFilter['retailer'])){
            $retailerId = $this->_defaultFilter['retailer'];
        } else {
            $retailerId = Testimonial_MageDoc_Helper_Price::AGGREGATOR_RETAILER_ID;
        }

        $this->setRetailerImportModel($retailerId);
        $this->setCollection($this->getImportModel()->getCollection());
        
        parent::_prepareCollection();
        return $this;
    }

    
    protected function _prepareColumns() 
    {        
       $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

        $this->addColumn('art_id', array(
           'header' => Mage::helper('magedoc')->__('Tecdoc Id'),
           'width' => '80px',
           'index' => 'art_id',
           'type' => 'currency'
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('magedoc')->__('Name'),
            'index' => 'name'
        ));

        $this->addColumn('model', array(
            'header' => Mage::helper('magedoc')->__('Model'),
            'index' => 'model',
            'filter_index' => 'main_table.model'
        ));

        $this->addColumn('art_article_nr',
            array(
                'header'=> Mage::helper('magedoc')->__('Code'),
                'width' => '100px',
                'index' => 'art_article_nr',
                'column_css_class' => 'a-right',
                'filter_condition_callback' => array($this, 'getNormalizedNumberFilterCallback')
        ));

        $this->addColumn('base_cost', array(
            'width' => '100px',
            'header' => Mage::helper('magedoc')->__('Cost'),
            'index' => 'base_cost',
            'filter_index' => 'cost',
            'type' => 'currency',
            'currency_code' => $currencyCode,

        ));

        $this->addColumn('base_price', array(
            'width' => '100px',
            'header' => Mage::helper('magedoc')->__('Price'),
            'index' => 'base_price',
            'filter_index' => 'price',
            'type' => 'currency',
            'currency_code' => $currencyCode,
        ));


        $this->addColumn('product_price', array(
            'width' => '100px',
            'header' => Mage::helper('magedoc')->__('Product price'),
            'index' => 'product_price',
            'filter_index' => 'catalog_product_price.value',
            'type' => 'currency',
            'currency_code' => $currencyCode,
        ));

        $this->addColumn('margin', array(
            'width' => '100px',
            'header' => Mage::helper('magedoc')->__('Margin'),
            'filter' => false,
            'sortable'  => false,
            'index' => 'margin',
            'type' => 'currency',
            'frame_callback' => array($this, 'wrapMarginValue')
                ));

        $this->addColumn('discount', array(
            'width' => '100px',
            'header' => Mage::helper('magedoc')->__('Discount'),
            'filter' => false,
            'sortable'  => false,
            'index' => 'discount',
            'type' => 'currency',
            'frame_callback' => array($this, 'wrapDiscountValue')
                ));


        $this->addColumn('final_price', array(
            'width' => '100px',
            'header' => Mage::helper('magedoc')->__('Final price'),
            'filter' => false,
            'sortable'  => false,
            'index' => 'final_price',
            'type' => 'currency',
            'currency_code' => $currencyCode,
            'frame_callback' => array($this, 'wrapFinalPriceValue')
                ));

        $this->addColumn('qty', array(
            'width' => '40px',
            'header' => Mage::helper('magedoc')->__('Qty'),
            'filter' => false,
            'sortable'  => false,
            'index' => 'qty',
            'type' => 'currency',
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('magedoc')->__('SKU'),
            'width' => '150px',
            'index' => 'sku',
        ));

        $this->addColumn('supplier', array(
            'header' => Mage::helper('magedoc')->__('Supplier'),
            'width' => '80px',
            'index' => 'supplier_id',
            'type' => 'options',
            'options' => $this->_getCurrentDirectory()->getSupplierOptions(),
        ));

        $this->addColumn('retailer', array(
            'header' => Mage::helper('magedoc')->__('Retailer'),
            'width' => '80px',
            'type' => 'options',
            'index' => 'retailer_id',
            'filter_index' => 'main_table.retailer_id',
            'options' => Mage::getSingleton('magedoc/source_retailer')->getOptionArray(),
            'filter_condition_callback' => array($this, '_getRetailerFilterCallback'),
            'frame_callback' => array($this, 'wrapRetailerValue')
        ));


        $this->addColumn('category', array(
            'header' => Mage::helper('magedoc')->__('Category'),
            'width' => '200px',
            'type' => 'options',
            'index' => 'category_id',
            'filter_index' => 'catalog_category_entity.entity_id',
            'options' => Mage::getSingleton('magedoc/adminhtml_system_config_source_category')->getOptionArray(),
        ));

        $this->addColumn('is_imported', array(
            'header' => Mage::helper('magedoc')->__('Is imported'),
            'width' => '40px',
            'index' => 'is_imported',
            'type' => 'options',
            'options' => array('1' => $this->__('Yes'), '0' => $this->__('No')),
            'filter_condition_callback' => array($this, 'getStatusFilterCallback'),
        ));

        
        return parent::_prepareColumns();
    }
    
    public function getNormalizedNumberFilterCallback($collection, $column)
    {        
        if($column->getFilter()->getValue()){
            $codeNormalized = Mage::helper('magedoc')->normalizeCode( $column->getFilter()->getValue() );
            if (Mage::helper('core')->isModuleEnabled('MageDoc_DirectoryTecdoc')){
                Mage::getResourceSingleton('magedoc/tecdoc_article_collection')
                    ->joinArticlesNormalized($collection, 'main_table');
                $collection->getSelect()
                    ->where("td_article_normalized.ARN_ARTICLE_NR_NORMALIZED LIKE '{$codeNormalized}%'");
            } else {
                $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
                $collection->addFieldToFilter($field, array ('like' => $codeNormalized.'%'));
            }
        }
        return $this;        
    }
    
    
    public function getStatusFilterCallback($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();
        if($cond['eq']){
            $collection->getSelect()->where('catalog_product_entity.entity_id IS NOT NULL');
        }else{
            $collection->getSelect()->where('catalog_product_entity.entity_id IS NULL');
        }
        return $this;
    }
        
    public function getImportedStatusCallback($row)
    {
        return $row->getData('product_id') !== null ? 1 : 0;
    }       
    
    
    protected function _getRetailerFilterCallback($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();
        if($cond['eq'] != 0){
            $this->getCollection()->addFieldToFilter('main_table.retailer_id', $cond);
        }
        return $this;
    }
    

    protected function _prepareMassaction() {
        $this->setMassactionIdField('collection_id');
        $this->getMassactionBlock()->setFormFieldName('magedoc');

        $this->getMassactionBlock()->addItem('import', array(
            'label' => Mage::helper('magedoc')->__('Import Products'),
            'url' => $this->getUrl('*/*/massImport', array('_current' => true))
        ));
        
        $this->getMassactionBlock()->addItem('update', array(
            'label' => Mage::helper('magedoc')->__('Update Products'),
            'url' => $this->getUrl('*/*/massUpdate', array('_current' => true))
        ));

        
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getDataId()));
    }

    protected function _getCurrentDirectory()
    {
        return Mage::registry('magedoc_directory');
    }

    public function wrapDiscountValue($renderedValue, $row, $column)
    {
        return $renderedValue
            ? ($row->getPriceWithDiscount() > $row->getPriceWithMargin() ? "<strong>{$renderedValue}</strong>" : $renderedValue)
            : '';
    }

    public function wrapMarginValue($renderedValue, $row, $column)
    {
        return $renderedValue
            ? ($row->getPriceWithMargin() >  $row->getPriceWithDiscount() ? "<strong>{$renderedValue}</strong>" : $renderedValue)
            : '';
    }

    public function wrapRetailerValue($renderedValue, $row, $column)
    {
        if ($row->getMinDiscountedPriceRetailerId()
            && $row->getMinDiscountedPriceRetailerId() != $row->getRetailerId()){
            $secondaryRetailerName = Mage::helper('magedoc/price')->getRetailerById($row->getMinDiscountedPriceRetailerId())->getName();
        }
        return $renderedValue
            ? (isset($secondaryRetailerName) ? "{$renderedValue} ({$secondaryRetailerName})" : $renderedValue)
            : '';
    }

    public function wrapFinalPriceValue($renderedValue, $row, $column)
    {
        $hlp = Mage::helper('magedoc/price');
        $percent = $hlp->getSignificantDeviationPercent();
        if (!$row->getFinalPrice()
            || !$row->getProductPrice()
            || !$percent){
            return $renderedValue;
        }
        return $renderedValue
            ? ($row->getFinalPrice() >  $row->getProductPrice() * (100 + $percent) / 100
                ? "<span class=\"incr\">{$renderedValue}</span>"
                : ($row->getFinalPrice() <  $row->getProductPrice() * (100 - $percent) / 100
                    ? "<span class=\"decr\">{$renderedValue}</span>"
                    : $renderedValue))
            : '';
    }
}
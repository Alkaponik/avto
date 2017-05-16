<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Create_Search_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid
{
    protected $_types;
    protected function _setFilterValues($data)
    {
        if(isset($data['types'])){   
            $this->_types = $data['types'];
            $this->getCollection()->getSelect()
                ->joinLeft(array('type_product' => 'magedoc_type_product'),
                    "type_product.product_id = e.entity_id AND type_product.type_id IN($this->_types)",
                    array())
                ->where('e.td_art_id IS NULL OR type_product.product_id IS NOT NULL');
        }
        return parent::_setFilterValues($data);
    }
    
    public function getTypeIds()
    {
        $filter   = $this->getParam($this->getVarNameFilter(), null);

        if (is_null($filter)) {
            return null;
        }

        if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                return isset($data['types']) ? $data['types'] : null;
        }
        return null;
    }

    
    
    protected function _prepareCollection()
    {
        $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
        $collection = Mage::getResourceModel('magedoc/catalog_product_collection');//->getCollection();
        $collection
            ->setStore($this->getStore())
            ->addAttributeToSelect($attributes)
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('retailer_id', true)
            ->addAttributeToSelect('supplier', true)
            ->addAttributeToSelect('manufacturer', true)
            ->addAttributeToSelect('cost', true)
            ->addStoreFilter()
            ->addAttributeToFilter('type_id', array_keys(
                Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')->asArray()
            ))
            ->addAttributeToSelect('gift_message_available');
        
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        $collection->joinTable(
                array('category_product' => $collection->getConnection()->getTableName('catalog/category_product_index')),
                "product_id = entity_id",
                array('category_id' => 'category_id'),
                array('store_id' => $this->getStore()->getId()),
                'left');
        $root = Mage::app()->getStore($this->getStore())->getRootCategoryId();
        if (Mage::helper('core')->isModuleEnabled('MageDoc_DirectoryTecdoc')) {
            $collection->getSelect()
                ->joinLeft(array('article' => Mage::getResourceSingleton('magedoc/tecdoc_article')->getTable('magedoc/tecdoc_article')),
                    'article.ART_ID = td_art_id',
                    array('art_article_nr' => 'article.ART_ARTICLE_NR'));
        }
        $collection->getSelect()
            ->where("category_product.category_id != {$root}")
            ->group('e.entity_id');

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        /*$this->setColumnFilters(
            array(
                'combobox'  =>  'magedoc/adminhtml_widget_grid_column_filter_combobox'
            )
        )*/;

        $manufacturers = array();
        foreach (Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer')->getSource()->getAllOptions() as $manufacturer){
            $manufacturers[$manufacturer['value']] = $manufacturer['label'];
        }

        $this->addColumnAfter('cost', array(
            'header'    => Mage::helper('magedoc')->__('Cost'),
            'column_css_class' => 'price',
            'align'     => 'center',
            'type'      => 'currency',
            'index'     => 'cost',
        ), 'sku');
        
        $this->addColumnAfter('manufacturer', array(
            'header'    => Mage::helper('magedoc')->__('Supplier'),
            'width'     => '80px',
            'filter'    => 'magedoc/adminhtml_widget_grid_column_filter_combobox',
            'style'     => 'width:80px',
            'input_style'=> 'width:60px',
            'index'     => 'manufacturer',
            'type'      => 'options',
            'filter_condition_callback' => array($this, 'getManufacturerFilterCallback'),
            //'options'   => Mage::getSingleton('magedoc/source_supplier')->getOptionArray(),
            'options'   => $manufacturers
        ), 'price');

        $this->addColumnAfter('retailer', array(
            'header'    => Mage::helper('magedoc')->__('Retailer'),
            'width'     => '80px',
            'index'     => 'retailer_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('magedoc/source_retailer')->getOptionArray(),
            'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_options_select',
            'name'      => 'retailer_id',
            'element_css_class' => 'input-text'
        ), 'supplier');


        $this->addColumnAfter('category', array(
            'header' => Mage::helper('magedoc')->__('Category'),
            'width' => '200px',
            'type' => 'options',
            'index' => 'category_id',            
            'options' => Mage::getSingleton('magedoc/source_category')->getOptionArray(),
            'filter_condition_callback' => array($this, 'getCategoryFilterCallback'),
        ), 'retailer');

        $this->addColumnAfter('number', array(
            'header' => Mage::helper('magedoc')->__('Number'),
            'width' => '200px',
            'type' => 'text',
            'index' => 'art_article_nr',  
//            'filter_index' => 'article.ART_ARTICLE_NR',
            'filter_condition_callback' => array($this, 'getNumberCrossFilterCallback'),
        ), 'retailer');
        
        $this->addColumn('information', array(
            'header' => Mage::helper('magedoc')->__('Information'),
            'width' => '20px',
            'type'  => 'action',
            'actions'   => array(
                    array(
                        'caption' => Mage::helper('magedoc')->__('Information'),
                        'popup' => true,
                    )
                ),
            'filter'    => false,
            'sortable'  => false,
            'type_id'   => $this->getTypeIds(),
            'renderer' => 'magedoc/adminhtml_widget_grid_column_renderer_action',
        ));


        $this->sortColumnsByOrder();
    }
    
    public function getCategoryFilterCallback($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();
        if(isset($cond['eq'])){
            $collection->getSelect()->where("category_product.category_id = {$cond['eq']}");
        }
        return $this;
    }
    
    public function getNumberCrossFilterCallback($collection, $column)
    {        
        if($column->getFilter()->getValue()){
            $numberNormalized = preg_replace('/[^a-zA-Z0-9]*/', '', $column->getFilter()->getValue());
            $collection->getSelect()->joinInner(array('lookup' => Mage::getResourceSingleton('magedoc/tecdoc_article')->getTable('magedoc/tecdoc_artLookup')),
                "lookup.ARL_SEARCH_NUMBER = '{$numberNormalized}' AND ARL_ART_ID = td_art_id AND ARL_KIND IN (1,2,4)", array());
        }
        return $this;        
    }

    public function getManufacturerFilterCallback($collection, $column)
    {
        $filterResource = Mage::getResourceModel('catalog/layer_filter_attribute');
        $filter = $column->getFilter();
        $layer = new Varien_Object(array(
            'product_collection' => $collection));
        $filter->setLayer($layer);
        $filter->setAttributeModel(Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer'));
        $filterResource->applyFilterToCollection($filter, $column->getFilter()->getValue());
    }
    
}

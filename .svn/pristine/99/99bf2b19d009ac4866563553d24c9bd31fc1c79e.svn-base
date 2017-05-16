<?php

class Testimonial_MageDoc_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('articleGrid');
        $this->setDefaultSort('ART_ID');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('article_filter');
        $this->_defaultFilter = array();
        $supplierOptions = Mage::getSingleton('magedoc/source_supplier')->getOptionArray();
        if ($supplierFilterValue = key($supplierOptions)){
            $this->_defaultFilter['supplier'] = $supplierFilterValue;
        }
        $categoryOptions = Mage::getSingleton('magedoc/adminhtml_system_config_source_tecdocCategory')->getOptionArray();
        if ($categoryFilterValue = key($categoryOptions)){
            $this->_defaultFilter['category'] = $categoryFilterValue;
        }
    }
    
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getResourceModel('magedoc/tecdoc_article_collection');
        
        $collection->joinProductsWithCategory()
                ->joinAttribute('sku')
                ->joinAttribute('name')
                ->joinAttribute('status');
        $collection->getSelect()->columns('ART_ID as art_id');
        $collection->getSelect()->columns('ART_ARTICLE_NR as art_article_nr');
        //print_r((string)$collection->getSelect());die;
        $collection->getSelect()->group('art_id');

        $this->setCollection($collection);

        parent::_prepareCollection();
  
        return $this;
    }

    
    
  
    protected function _prepareColumns()
    {

        $this->addColumn('art_id',
            array(
                'header'=> Mage::helper('magedoc')->__('Tecdoc Id'),
                'width' => '80px',
                'index' => 'art_id',
                'filter_index' => 'main_table.ART_ID',
                'type'         => 'currency'
        ));

       $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
        ));

       $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '150px',
                'index' => 'sku',
        ));

       $this->addColumn('art_article_nr',
            array(
                'header'=> Mage::helper('catalog')->__('Code'),
                'width' => '80px',
                'index' => 'art_article_nr',
                'column_css_class' => 'a-right',
                'filter_condition_callback' => array($this, 'getNormalizedNumberFilterCallback'),
        ));

       $this->addColumn('supplier',
            array(
                'header'=> Mage::helper('magedoc')->__('Supplier'),
                'width' => '80px',
                'index' => 'art_sup_id',
                'type'  => 'options',
                'filter_index' => 'td_sup_id',
                'options' => Mage::getSingleton('magedoc/source_supplier')->getOptionArray()
        ));
       
       
        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
//                'filter_index' => 'catalog_product_entity_int.value',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray()
        ));


        $this->addColumn('category',
                array(
                    'header'=> Mage::helper('catalog')->__('Category'),
                    'width' => '200px',
                    'type'  => 'options',
                    'index' => 'category_id',
                    'filter_index' => 'catalog_category_entity.entity_id',
                    'options'=> Mage::getSingleton('magedoc/adminhtml_system_config_source_tecdocCategory')->getOptionArray()
         ));


        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores'
        ));


        return parent::_prepareColumns();
    }

    public function getNormalizedNumberFilterCallback($collection, $column)
    {
        if($column->getFilter()->getValue()) {
            $codeNormalized = Mage::helper('magedoc')->normalizeCode( $column->getFilter()->getValue() );
            $this->getCollection()->getSelect()
                ->where("td_article_normalized.ARN_ARTICLE_NR_NORMALIZED LIKE '{$codeNormalized}%'");
        }
        return $this;        
    }

    
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'store'=>$this->getRequest()->getParam('store'),
            'id'=>$row->getId())
        );
    }
}

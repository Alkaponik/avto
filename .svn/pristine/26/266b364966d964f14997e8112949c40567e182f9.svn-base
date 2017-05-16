<?php

class Testimonial_MageDoc_Block_Adminhtml_Product_Map_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    const SUGGEST_ACTION_NAME = 'suggest';

    /** @var Testimonial_Magedoc_Model_Mysql4_Supplier_Map_Collection   _collection */
    protected $_collection;
    protected $_collectionModelName = 'magedoc/import_retailer_data_collection';
    protected $_directory;

    public function __construct()
    {
        parent::__construct();
        $this->setId('product_map');
        $this->setSaveParametersInSession(true);
    }

    protected function _isSuggestAction()
    {
        return $this->getRequest()->getActionName() == static::SUGGEST_ACTION_NAME;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_collectionModelName);
        $resource = $collection->getResource();

        $this->getCurrentDirectory()->getResource()->joinProducts ( $collection->getSelect() );

        $collection->getSelect()
            ->joinLeft(
                array('directory_offer_link' => $resource->getTable('magedoc/directory_offer_link')),
                "directory_offer_link.data_id = main_table.data_id
                            AND directory_offer_link.directory_code = '{$this->getCurrentDirectory()->getCode()}'",
                array('directory_offer_link.supplier_id', 'directory_offer_link.directory_entity_id')
            );
        $collection->addFilterToMap('supplier_id', 'directory_offer_link.supplier_id');
        $collection->addFilterToMap(
            'directory_entity_id',
            new Zend_Db_Expr('IF(directory_offer_link.directory_entity_id IS NULL, 0, 1)')
        );

        if ($this->getCurrentDirectory()->canSuggestProducts()
            && $this->_isSuggestAction()) {
            try {
                $this->getCurrentDirectory()->joinDirectoryProductsSuggestions($collection);
                $collection->getSelect()->group('main_table.data_id');
            } catch (Exception $e){
                Mage::logException($e);
            }
        } else {
            $this->getCurrentDirectory()->getResource()->joinProductName($collection, 'product_name');
            $resource = $collection->getResource();
            $collection->getSelect()
                ->joinInner(
                    array('retailer' => $resource->getTable('magedoc/retailer')),
                    'retailer.retailer_id = main_table.retailer_id AND retailer.enabled = 1',
                    ''
                );

            $this->getCurrentDirectory()->joinSuppliers($collection->getSelect());
        }

        $this->_prepareCollectionAfter($collection);

        $this->setCollection($collection);

        parent::_prepareCollection();
    }

    protected function _prepareGrid()
    {
        $this->_prepareColumns();
        $this->_prepareMassactionBlock();
        $this->_prepareCollection();
        $this->_prepareJavascript();
        return $this;
    }

    protected function _prepareJavascript()
    {
        if(false) {
        $javaScript = <<<JS
            $$('.suggested-product').each(function(element) {
                element.on('click', function(event) {
                    var elementValue =  element.innerHTML;
                    var options = element.up('tr').down('td.product_id_container select').childElements();
                    for(option in options) {
                        option.selected = false;
                    }

                    options[0].selected = 'selected';
                    for(option in options) {
                        if(options[option].innerHTML  == elementValue) {
                            options[option].selected = 'selected';
                        }
                    }

                });
             });
JS;
        } else {
            $javaScript = <<<JS
            $$('.suggested-product').each(function(element) {
                element.on('click', function(event) {
                    var comboContainer =  element.up('tr').down('td.product_id_container .combo-container');
                    var combobox =   eval(comboContainer.id + '_combobox');
                    combobox.input.value = element.innerHTML;
                    combobox.filter(element.innerHTML);
                    var productId = $(element).readAttribute('data-product_id');
                    if (combobox.select.options.length == 0 && productId){
                        combobox.addOptionToSelect(element.innerHTML, productId, true);
                    }
                    /*
                    combobox.getRequestData(null,null,null,null,null,
                        function(combobox, text, value) {
                             text.value = value;
                             combobox.filter(value);
                        }.curry(combobox, comboContainer.down('input[type=text]'), element.innerHTML));*/
                });
             });
JS;
        }

        if($directory  = $this->getCurrentDirectory()) {
            $supplierId = $this->getColumn('supplier_id')->getFilter()->getValue();
            $products = $supplierId
                ? $this->getManufacturerProductOptions($supplierId)
                : array();

            $products[0] = Mage::helper('magedoc')->__('--Not linked to directory--');
            $products = json_encode($products);
            $javaScript .= <<<JS
            var productOptions = $products;
            if(typeof window.{$this->getId()}_product_idComboboxStorage === 'undefined') {
                window.{$this->getId()}_product_idComboboxStorage = [];
            }
            for (var i = 0; i < window.{$this->getId()}_product_idComboboxStorage.length; i++) {
                window.{$this->getId()}_product_idComboboxStorage[i].data = productOptions;
                window.{$this->getId()}_product_idComboboxStorage[i].url = '';
            }
JS;
        }

        $this->setAdditionalJavaScript($javaScript);
    }

    protected function _prepareCollectionAfter($collection)
    {

    }

    protected function _getSuppliersList()
    {
        $sourceSupplierList = $this->getCurrentDirectory()->getSupplierOptions();
        $supplierList = array( Mage::helper('magedoc')->__('--Not linked to directory--') );
        foreach($sourceSupplierList as $key=>$value) {
            $supplierList[$key] = $value;
        }

        return $supplierList;
    }

    /**
     * @return Testimonial_MageDoc_Model_Directory_Abstract
     * @throws Mage_Core_Exception
     */

    public function getCurrentDirectory()
    {
        if(is_null($this->_directory)) {
            if( $directoryId = $this->_getCurrentDirectoryId() ) {
                $this->_directory
                    = Mage::getSingleton('magedoc/directory')->getDirectory( $directoryId );
            } else {
                Mage::throwException(Mage::helper('magedoc')->__('Directory was not selected'));
            }
        }

        return $this->_directory;
    }

    protected function  _getCurrentDirectoryId()
    {
        return $this->getRequest()->getParam('directory') ? : Mage::helper('magedoc')->getDefaultDirectoryCode();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('data_id',
            array(
                'header'    => Mage::helper('magedoc')->__('ID'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'data_id',
            )
        );

        $this->addColumn(
            'directory_entity_id',
            array(
                'header' => Mage::helper('magedoc')->__('Directory Id'),
                'width'  => '80px',
                'index'  => 'directory_entity_id',
                'filter' => 'adminhtml/widget_grid_column_filter_select',
                'options' => array('1' => $this->__('Yes'), '0' => $this->__('No')),
            )
        );

        $this->addColumn('name',
            array(
                'header'    => Mage::helper('magedoc')->__('Name'),
                'index'     => 'name',
                'filter_index'     => 'main_table.name',
                'width'     => '300px',
            )
        );

        $this->addColumn('manufacturer',
            array(
                'header'    => Mage::helper('magedoc')->__('Supplier'),
                'index'     => 'manufacturer',
                'width'     => '100px',
                'filter_index'     => 'main_table.manufacturer',
                'column_css_class' => 'retailer_supplier_map',
            )
        );

        $this->addColumn('code',
            array(
                'header'    => Mage::helper('magedoc')->__('Code'),
                'index'     => 'code',
                'width'     => '150px',
                'filter_index'  => 'code_normalized',
                'filter_condition_callback' => array($this, 'getNormalizedNumberFilterCallback'),
            )
        );

        $supplierList = $this->_getSuppliersList();

        $this->addColumn('supplier_id',
            array(
                'header'    => Mage::helper('magedoc')->__('Supplier'),
                'type'      => 'options',
                'index'     => 'supplier_id',
                'width'     => '100px',
                'element_style'=> 'min-width:100px;width:100%;',
                'filter_index' => 'supplier_id',
                'filter'    => 'magedoc/adminhtml_widget_grid_column_filter_combobox',
                'totals_label' => '',
                'column_css_class' => 'supplier_directory_id_container',
                /*'filter_condition_callback' => array($this, 'getSupplierFilterCallback'),*/
                'options' => $supplierList,
            )
        );

        if($this->_isSuggestAction()) {
            $this->addColumn('product_name_suggested',
                array(
                    'header'    => Mage::helper('magedoc')->__('Suggested Products'),
                    'align'     =>'right',
                    'width'     => '80px',
                    'index'     => 'product_name_suggested',
                    'filter'    => false,
                    'column_css_class' => 'retailer_supplier_map',
                    'frame_callback' => array($this, 'wrapSuggestedProducts'),
                )
            );
        }

            $productIdColumnOptions =
                array(
                    'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_combobox',                    
                    'settings'  => array(
                        'delayOptionsInitialization' => true,
                    ),
                    'source_url'                 =>
                        Mage::getUrl('magedoc/adminhtml_product_map/productlist', array('directory' => $this->getCurrentDirectory()->getCode())),
                    'internal_options' => array(),
                );

        $this->addColumn('product_id',
            array_merge(array(
                'header'    => Mage::helper('magedoc')->__('Product'),
                'type'      => 'options',
                'index'     => 'product_id',
                'text_index'=> 'product_name',
                'element_style'=> 'min-width:100px;width:100%;',
                'filter'       => 'adminhtml/widget_grid_column_filter_text',
                'filter_index' => 'product_name',
                'sortable'     => false,
                'totals_label' => '',
                'column_css_class' => 'product_id_container',
                'options' => array(),
            ), $productIdColumnOptions)
        );

        $this->addColumn('retailer_id',
            array(
                'header'    => Mage::helper('magedoc')->__('Retailer'),
                'type'      => 'options',
                'index'     => 'retailer_id',
                'width'     => '100px',
                'filter_index' => 'main_table.retailer_id',
                'options'   => Mage::getModel('magedoc/source_retailer')->getOptionArray(),
                'internal_options' => Mage::getModel('magedoc/source_retailer')->getOptionArray(),
                'totals_label' => '',
            )
        );

        /*$this->addColumn('use_crosses',
            array(
                 'header'    => Mage::helper('magedoc')->__('Use crosses'),
                 'index'     => 'use_crosses',
                 'width'     => '120px',
                 'type'  => 'checkbox',
                 'values'   => '1',
                 'value'   => '1',
                 'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_checkbox',
            )
        );*/

        $this->addColumn('created_at',
            array(
                'header'    => Mage::helper('magedoc')->__('created_at'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'created_at',
                'type'      => 'datetime',
                'totals_label' => '',
            )
        );

        return parent::_prepareColumns();
    }

    public function wrapSuggestedProducts($value, $row, $column, $isExport)
    {
        $values = explode(',', $value);
        $productIds = explode(',', $row->getProductIdSuggested());
        $options = array();
        foreach ($values as $key => $value ){
            $productId = isset($productIds[$key])
                ? $productIds[$key]
                : null;
            $options []= "<span class='suggested-product' data-product_id=\"{$productId}\">{$value}</span>";
        }
        return implode(', ', $options);
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        $this->setTotals(new Varien_Object());
        return parent::_afterLoadCollection();
    }

    public function getRowUrl($row)
    {
        return null;
    }

    public function getGridUrl()
    {
        return $this->getCurrentUrl( array('remove_switcher' => 1) );
    }

    public function getNormalizedNumberFilterCallback($collection, $column)
    {
        if($column->getFilter()->getValue()) {
            $codeNormalized = Mage::helper('magedoc')->normalizeCode( $column->getFilter()->getValue() );
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
            $collection->addFieldToFilter($field, array ('like' => $codeNormalized.'%'));
        }
        return $this;
    }

    public function getSupplierFilterCallback($collection, $column)
    {
        if($column->getFilter()->getValue() &&
            $this->_isSuggestAction()) {
        }
        return $this;
    }

    public function getManufacturerProductOptions($manufacturer)
    {
        return $this->getCurrentDirectory()->getProductOptions($manufacturer);
    }
}
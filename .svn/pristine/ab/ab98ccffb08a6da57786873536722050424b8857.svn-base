<?php

class Testimonial_MageDoc_Block_Adminhtml_Supplier_Map_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    const SUGGEST_ACTION_NAME = 'suggest';

    /** @var Testimonial_Magedoc_Model_Mysql4_Supplier_Map_Collection   _collection */
    protected $_collection;
    protected $_collectionModelName = 'magedoc/supplier_map_collection';
    protected $_directory;

    public function __construct()
    {
        parent::__construct();
        $this->setId('mapItem');
        $this->setSaveParametersInSession(true);
    }

    protected function _isSuggestAction()
    {
        return $this->getRequest()->getActionName() == static::SUGGEST_ACTION_NAME;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_collectionModelName)
            ->addFieldToFilter('main_table.directory_code', $this->_getCurrentDirectoryId());

        if ($this->_isSuggestAction()) {
            $this->_getCurrentDirectory()->joinDirectorySuppliersSuggestions($collection);
        } else {
            $resource = $collection->getResource();
            $collection->getSelect()
                ->joinLeft(
                    array('base' => $resource->getTable('magedoc/import_retailer_data_base')),
                    'base.manufacturer = main_table.manufacturer AND base.retailer_id = main_table.retailer_id',
                    'count( base.data_id ) as supplier_id_count'
                )
                ->joinLeft(
                    array('preview' => $resource->getTable('magedoc/import_retailer_data_preview')),
                    'preview.code_raw = base.code_raw
                            AND preview.retailer_id = base.retailer_id
                            AND preview.manufacturer = base.manufacturer AND preview.source_id = base.source_id ',
                    ''
                )
                ->joinLeft(
                    array('directory_offer_link' => $resource->getTable('magedoc/directory_offer_link_preview')),
                    'directory_offer_link.data_id = preview.data_id
                            AND directory_offer_link.directory_code = main_table.directory_code',
                    'COUNT( directory_offer_link.directory_entity_id ) as td_art_id_count,
                         ( COUNT( base.data_id ) - COUNT( directory_offer_link.directory_entity_id ) ) as not_linked_count'
                )
                ->joinInner(
                    array('retailer' => $resource->getTable('magedoc/retailer')),
                    'retailer.retailer_id = main_table.retailer_id AND retailer.enabled = 1',
                    ''
                );

            $this->_getCurrentDirectory()->joinSuppliers($collection->getSelect());

            $collection->addFilterToMap('td_art_id_count', new Zend_Db_Expr('SUM( IF( directory_offer_link.directory_entity_id IS NULL, 0, 1 ) )'));
            $collection->addFilterToMap('supplier_id_count', new Zend_Db_Expr('count( preview.data_id )'));
            $collection->addFilterToMap(
                'supplier_id_count',
                new Zend_Db_Expr('(count( preview.data_id ) - SUM( IF( preview.td_art_id IS NULL , 0, 1 ) ))')
            );
        }

        $collection->getSelect()->group('map_id');

        $collection->addFilterToMap('supplier_id', 'IFNULL(main_table.supplier_id, 0)');

        $collection->addFilterToMap(
            'supplier_name_suggested',
            '`supplier2`.`' .
            $this->_getCurrentDirectory()->getResource()->getKeyField('vendor', 'name') .
            '`');
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
        if(!$this->_getCurrentDirectory()->isCustomManufacturersAllowed()) {
        $javaScript = <<<JS
            $$('.suggested-brand').each(function(element) {
                element.on('click', function(event) {
                    var elementValue =  element.innerHTML;
                    var options = element.up('tr').down('td.supplier_directory_id_container select').childElements();
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
            $$('.suggested-brand').each(function(element) {
                element.on('click', function(event) {
                    var comboContainer =  element.up('tr').down('td.supplier_directory_id_container .combo-container');
                    var combobox =   eval(comboContainer.id + '_combobox');
                    combobox.input.value = element.innerHTML;
                    combobox.filter(element.innerHTML);
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

        if($directory  = $this->_getCurrentDirectory()) {
            $suppliers  = $directory->getSupplierOptions();
            $suppliers[0] = Mage::helper('magedoc')->__('--Not linked to directory--');
            $suppliers = json_encode($suppliers);
            $javaScript .= <<<JS
            var supplierOptions = $suppliers;
            for (var i = 0; i < window.{$this->getId()}_supplier_idComboboxStorage.length; i++) {
                window.{$this->getId()}_supplier_idComboboxStorage[i].data = supplierOptions;
            }
JS;
        }

        $this->setAdditionalJavaScript($javaScript);
    }

    protected function _afterToHtml($html)
    {
        return $this->canDisplayContainer()
            ? $html
            : $html.'<script type="text/javascript">'.$this->getAdditionalJavaScript().'</script>';
    }

    protected function _reconciliationSupplierIdCountFilterCallback($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();
        if ($cond){
            $collection->addFieldToHavingFilter('supplier_id_count', $cond);
        }
    }

    protected function _reconciliationTdArtIdCountFilterCallback($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();
        if ($cond){
            $collection->addFieldToHavingFilter('td_art_id_count', $cond);
        }
    }

    protected function _reconciliationNotLinkedCountFilterCallback($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();
        if ($cond){
            $collection->addFieldToHavingFilter('supplier_id_count', $cond);
        }
    }

    protected function _prepareCollectionAfter($collection)
    {

    }

    protected function _getSuppliersList()
    {
        $sourceSupplierList = $this->_getCurrentDirectory()->getSupplierOptions();
        $supplierList = array( Mage::helper('magedoc')->__('--Not linked to directory--') );
        foreach($sourceSupplierList as $key=>$value) {
            $supplierList[$key] = $value;
        }

        return $supplierList;
    }

    protected function _getCurrentDirectory()
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
        $this->addColumn('map_id',
            array(
                'header'    => Mage::helper('magedoc')->__('ID'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'map_id',
            )
        );

        $this->addColumn('manufacturer',
            array(
                'header'    => Mage::helper('magedoc')->__('Supplier'),
                'align'     =>'right',
                'width'     => '80px',
                'index'     => 'manufacturer',
                'filter_index'     => 'main_table.manufacturer',
                'column_css_class' => 'retailer_supplier_map',
                'frame_callback' => array($this, 'wrapSuggestedSuppliers'),
            )
        );

        if($this->_isSuggestAction()) {
            $this->addColumn('supplier_name_suggested',
                array(
                    'header'    => Mage::helper('magedoc')->__('Suggested brands'),
                    'align'     =>'right',
                    'width'     => '80px',
                    'index'     => 'supplier_name_suggested',
                    'column_css_class' => 'retailer_supplier_map',
                    'frame_callback' => array($this, 'wrapSuggestedSuppliers'),
                )
            );
        }
        $supplierList = $this->_getSuppliersList();
        $supplierIdColumnOptions =
            array(
                'renderer'  =>  'magedoc/adminhtml_widget_grid_column_renderer_options_select',
            );

        if($this->_getCurrentDirectory()->isCustomManufacturersAllowed()) {
            $supplierIdColumnOptions =
                array(
                    'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_combobox',                    
                    'settings'  => array(
                        'delayOptionsInitialization' => true,
                        'withEmpty'                  => true
                    ),
                    'internal_options' => $supplierList,
                );
        }

        $this->addColumn('supplier_id',
            array_merge(array(
                'header'    => Mage::helper('magedoc')->__('Supplier'),
                'type'      => 'options',
                'index'     => 'supplier_id',
                'width'     => '100px',
                'element_style'=> 'min-width:100px;width:100%;',
                'filter_index' => 'supplier_id',
                'totals_label' => '',
                'column_css_class' => 'supplier_directory_id_container',
                'options' => $supplierList,
            ), $supplierIdColumnOptions)
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

        $this->addColumn('use_crosses',
            array(
                 'header'    => Mage::helper('magedoc')->__('Use crosses'),
                 'index'     => 'use_crosses',
                 'width'     => '120px',
                 'type'  => 'checkbox',
                 'values'   => '1',
                 'value'   => '1',
                 'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_checkbox',
            )
        );

        if(!$this->_isSuggestAction()) {
            $this->addColumn('supplier_id_count',
                array(
                     'header'       => Mage::helper('magedoc')->__('Total records'),
                     'width'        => '50px',
                     'index'        => 'supplier_id_count',
                     'totals_label' => '',
                     'element_css_class' => 'qty-edit',
                     'type'         => 'range',
                     'filter_condition_callback' =>  array($this, '_reconciliationNotLinkedCountFilterCallback')
                )
            );

            $this->addColumn('td_art_id_count',
                array(
                     'header'       => Mage::helper('magedoc')->__('Linked to directory '),
                     'width'        => '50px',
                     'index'        => 'td_art_id_count',
                     'totals_label' => '',
                     'element_css_class' => 'qty-edit',
                     'type'         => 'range',
                     'filter_condition_callback' =>  array($this, '_reconciliationTdArtIdCountFilterCallback')
                )
            );

            $this->addColumn('not_linked_count',
                array(
                     'header'       => Mage::helper('magedoc')->__('Not linked to directory '),
                     'width'        => '50px',
                     'index'        => 'not_linked_count',
                     'totals_label' => 'not_linked_count',
                     'element_css_class' => 'qty-edit',
                     'type'         => 'range',
                     'filter_condition_callback' =>  array($this, '_reconciliationTdArtIdCountFilterCallback')
                )
            );
        }

        $this->addColumn('code_delimiter',
            array(
                 'header'    => Mage::helper('magedoc')->__('Code delimiter'),
                 'width'     => '50px',
                 'element_css_class' => 'qty-edit',
                 'index'     => 'code_delimiter',
                 'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
                 'totals_label' => '',
            )
        );

        $this->addColumn('code_part_count',
            array(
                 'header'    => Mage::helper('magedoc')->__('Code part count'),
                 'width'     => '50px',
                 'element_css_class' => 'qty-edit',
                 'index'     => 'code_part_count',
                 'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
                 'totals_label' => '',
            )
        );

        $this->addColumn('prefix_length',
            array(
                'header'    => Mage::helper('magedoc')->__('Prefix length'),
                'width'     => '50px',
                'element_css_class' => 'qty-edit',
                'index'     => 'prefix_length',
                'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
                'totals_label' => '',
            )
        );

        $this->addColumn('suffix_length',
            array(
                'header'    => Mage::helper('magedoc')->__('Suffix length'),
                'align'     =>'right',
                'element_css_class' => 'qty-edit',
                'width'     => '50px',
                'index'     => 'suffix_length',
                'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
                'totals_label' => '',
            )
        );

        $this->addColumn('prefix',
            array(
                'header'    => Mage::helper('magedoc')->__('Prefix'),
                'width'     => '50px',
                'index'     => 'prefix',
                'type'      => 'text',
                'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
                'totals_label' => '',
                'element_css_class' => 'qty-edit',
            )
        );

        $this->addColumn('suffix',
            array(
                'header'    => Mage::helper('magedoc')->__('Suffix'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'suffix',
                'type'      => 'text',
                'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
                'totals_label' => '',
                'element_css_class' => 'qty-edit',
            )
        );

        $this->addColumn('alias',
            array(
                'header'    => Mage::helper('magedoc')->__('Alias'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'alias',
                'type'      => 'text',
                'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
                'totals_label' => '',
                'element_css_class' => 'qty-edit',
            )
        );

        $this->addColumn('discount_percent',
            array(
                'header'    => Mage::helper('magedoc')->__('Discount percent'),
                'align'     =>'right',
                'width'     => '40px',
                'element_css_class' => 'qty-edit',
                'index'     => 'discount_percent',
                'type'      => 'text',
                'renderer'  => 'magedoc/adminhtml_widget_grid_column_renderer_text_input',
                'totals_label' => '',
            )
        );

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

    public function wrapSuggestedSuppliers($value, $row, $column, $isExport)
    {
        $value = explode(',', $value);
        return "<span class='suggested-brand'>" . implode("</span>, <span class='suggested-brand'>", $value) . "</span>";
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


}
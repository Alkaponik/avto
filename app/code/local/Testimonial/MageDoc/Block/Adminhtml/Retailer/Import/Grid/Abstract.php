<?php
class Testimonial_MageDoc_Block_Adminhtml_Retailer_Import_Grid_Abstract extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_overlappedFields = array(
        'general_stock_qty', 'domestic_stock_qty', 'qty', 'cost', 'price', 'manufacturer', 'name'
    );

    protected $_collectionModelName = 'magedoc/retailer_data_import_preview_collection';

    protected function __prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_collectionModelName);

        $this->_joinPreviewTableToCollection($collection);
        $collection->addFilterToMap(
            'directory_entity_id',
            new Zend_Db_Expr('IF(directory_offer_link.directory_entity_id IS NULL, 0, 1)')
        );

        foreach($this->_overlappedFields as $field) {
            $collection->addFilterToMap($field, new Zend_Db_Expr("IFNULL(preview.$field, main_table.$field)"));
        }

        $this->setCollection($collection);
    }

    protected function _prepareCollection()
    {
        $this->__prepareCollection();
        //print_r((string)$this->getCollection()->getSelect());die;
        parent::_prepareCollection();
    }

    protected function _joinPreviewTableToCollection($collection)
    {
        $resource = $collection->getResource();

        $collection->getSelect()
            ->joinLeft(
                array('preview' => $resource->getTable('magedoc/import_retailer_data_preview')),
                'preview.code_raw = main_table.code_raw
                    AND preview.retailer_id = main_table.retailer_id
                    AND preview.manufacturer = main_table.manufacturer',
                $this->_getPreviewTableColumns()
            )
            ->joinLeft(
                array('directory_offer_link' => $resource->getTable('magedoc/directory_offer_link_preview')),
                "preview.data_id = directory_offer_link.data_id AND directory_offer_link.directory_code = '{$this->_getCurrentDirectoryId()}'",
                array('directory_entity_id','directory_code','supplier_id')
            );;
    }

    protected function _getPreviewTableColumns()
    {
        $fieldsToJoin = array(
            'code_normalized',
            'preview.td_art_id',
            'code',
            'IFNULL(preview.code_raw, main_table.code) as code_raw',
        );
        foreach($this->_overlappedFields as $field) {
            $fieldsToJoin[$field] = "IFNULL(preview.{$field}, main_table.{$field})";
        }
        return $fieldsToJoin;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('data_id', array(
            'header' => Mage::helper('magedoc')->__('ID'),
            'width' => '80px',
            'index' => 'data_id',
            'filter_index' => 'preview.data_id',
        ));

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

        $this->addColumn('card', array(
            'header' => Mage::helper('magedoc')->__('Card'),
            'index' => 'card',
            'filter_index' => 'main_table.card',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('magedoc')->__('Name'),
            'index' => 'name',
            'filter_index' => 'main_table.name',
        ));

        $this->addColumn('model',
            array(
                'header'=> Mage::helper('magedoc')->__('Model'),
                'index' => 'model',
                'filter_index' => 'preview.model'
            ));

        $this->addColumn(
            'code_raw',
            array(
                 'header' => Mage::helper('magedoc')->__('Raw Code'),
                 'width'  => '80px',
                 'index'  => 'code_raw',
                 'filter_index' => 'main_table.code_raw'
            )
        );

        $this->addColumn('code',
            array(
                'header'=> Mage::helper('magedoc')->__('Code'),
                'index' => 'code',
                'filter_index' => 'preview.code'
        ));

        $this->addColumn(
            'code_normalized',
            array(
                'header' => Mage::helper('magedoc')->__('Code Normalized'),
                'width'  => '80px',
                'index'  => 'code_normalized',
                'filter_index'  => 'preview.code_normalized',
                'filter_condition_callback' => array($this, 'getNormalizedNumberFilterCallback'),
            )
        );

        $this->addColumn('cost',
            array(
                 'header'=> Mage::helper('magedoc')->__('Cost'),
                 'index' => 'cost',
                 'type'  => 'range',
                 'filter_index' => 'preview.cost'
            )
        );

        $this->addColumn('price',
            array(
                 'header'=> Mage::helper('magedoc')->__('Price'),
                 'index' => 'price',
                 'type'  => 'range',
                 'filter_index' => 'preview.price'
            )
        );
        $supplierList = $this->_getSuppliersList();
        $this->addColumn('supplier_id',
            array(
                'header'=> Mage::helper('magedoc')->__('Supplier'),
                'index' => 'supplier_id',
                'type'  => 'options',
                'options' => $supplierList,
                'filter_index' => 'directory_offer_link.supplier_id'
            )
        );

        $this->addColumn('retailer_id',
            array(
                'header'=> Mage::helper('magedoc')->__('Retailer'),
                'index' => 'retailer_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('magedoc/source_retailer')->getOptionArray(),
                'filter_index' => 'main_table.retailer_id'
            ));

        $this->addColumn('manufacturer',
            array(
                'header'=> Mage::helper('magedoc')->__('Manufacturer'),
                'index' => 'manufacturer',
            ));

        $this->addColumn('domestic_stock_qty',
            array(
                 'header'=> Mage::helper('magedoc')->__('Domestic stock qty'),
                 'index' => 'domestic_stock_qty',
                 'type'  => 'range',
            )
        );

        $this->addColumn('general_stock_qty',
            array(
                 'header'=> Mage::helper('magedoc')->__('General stock qty'),
                 'index' => 'general_stock_qty',
                 'type'  => 'range',
            )
        );

        $this->addColumn('qty',
            array(
                 'header'=> Mage::helper('magedoc')->__('Qty'),
                 'index' => 'qty',
                 'type'  => 'range',
            )
        );
        $this->addColumn('description',
            array(
                'header'=> Mage::helper('magedoc')->__('Description'),
                'index' => 'description',
                'filter_index' => 'main_table.description'
            ));

        foreach($this->_getExtraFields() as $field) {
            $this->addColumn($field,
                array(
                     'header'=> Mage::helper('magedoc')->__(ucfirst($field)),
                     'index' => $field,
                     'filter_index' => 'extended.data'
                ));
        }

        $this->addColumn('created_at',
            array(
                'header'=> Mage::helper('magedoc')->__('Created at'),
                'index' => 'created_at',
                'type' => 'datetime',
                'filter_index' => 'main_table.created_at'
            ));

        $this->addColumn('updated_at',
            array(
                'header'=> Mage::helper('magedoc')->__('Updated at'),
                'index' => 'updated_at',
                'type'  => 'datetime',
            ));

        return parent::_prepareColumns();
    }

    protected function _getExtraFields()
    {
        return $this->_getCurrentDirectory()
            ->getExtraFields();
    }

    protected function  _getCurrentDirectoryId()
    {
        return $this->getRequest()->getParam('directory') ? : Mage::helper('magedoc')->getDefaultDirectoryCode();
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

    protected function _getSuppliersList()
    {
        $sourceSupplierList = $this->_getCurrentDirectory()->getSupplierOptions();
        $supplierList = array( Mage::helper('magedoc')->__('--Not linked to directory--') );
        foreach($sourceSupplierList as $key=>$value) {
            $supplierList[$key] = $value;
        }

        return $supplierList;
    }

    public function getNormalizedNumberFilterCallback($collection, $column)
    {
        if($column->getFilter()->getValue()) {
            $codeNormalized = Mage::helper('magedoc')->normalizeCode( $column->getFilter()->getValue() );
            $this->getCollection()->getSelect()
                ->where("preview.code_normalized LIKE '{$codeNormalized}%'");
        }
        return $this;
    }

    public function getRowUrl($row)
    {
        return null;
    }


}
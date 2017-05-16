<?php
class Testimonial_MageDoc_Block_Adminhtml_Retailer_Import_Session_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _prepareCollection()
    {
        if(!isset($this->_collection)){
            $this->_collection = Mage::getResourceModel('magedoc/retailer_data_import_session_collection');
        }
        $this->setCollection($this->_collection);

        $resource = Mage::getResourceSingleton('magedoc/tecdoc_article');
        $this->_collection->getSelect()
            ->joinLeft(
                array('source' => $resource->getTable('magedoc/retailer_data_import_session_source')),
                'source.session_id = main_table.session_id',
                ''
            )->joinLeft(
                array('config' => $resource->getTable('magedoc/retailer_data_import_adapter_config')),
                'source.config_id = config.config_id',
                ''
            )
            ->group('main_table.session_id');

        $this->_collection->getSelect()->columns("GROUP_CONCAT( CONCAT(SUBSTRING_INDEX(source_path, '\\".DS."', -1), ' (', IFNULL(config.name, ''),')') ) as price_source");

        $this->setDefaultSort('created_at');

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('session_id',
            array(
                'header'    => Mage::helper('magedoc')->__('Session Id'),
                'width'     => '20px',
                'index'     => 'session_id',
            )
        );

        $this->addColumn('retailer_id',
            array(
                'header'        => Mage::helper('magedoc')->__('Retailer'),
                'index'         => 'retailer_id',
                'filter_index'  => 'main_table.retailer_id',
                'type'          => 'options',
                'options'       => Mage::getSingleton('magedoc/source_retailer')->getOptionArray(),
            )
        );
        //var_dump( Mage::getSingleton('magedoc/source_retailer_data_import_adapter_config')); die;
        $this->addColumn('config_id',
            array(
                'header'        => Mage::helper('magedoc')->__('Adapter config'),
                'index'         => 'config_id',
                'filter_index'  => 'source.config_id',
                'type'          => 'options',
                'options'       => Mage::getSingleton('magedoc/source_retailer_data_import_adapter_config')->getOptionArray()
            )
        );

        $this->addColumn('total_records',
            array(
                'header'            => Mage::helper('magedoc')->__('Total records'),
                'index'             => 'total_records',
                'element_css_class' => 'qty-edit',
                'type'              => 'range'
            )
        );

        $this->addColumn('valid_records',
            array(
                'header'            => Mage::helper('magedoc')->__('Valid records'),
                'index'             => 'valid_records',
                'element_css_class' => 'qty-edit',
                'type'              => 'range'
            )
        );

        $this->addColumn('records_with_old_brands',
            array(
                'header'            => Mage::helper('magedoc')->__('Linked to brands'),
                'index'             => 'records_with_old_brands',
                'element_css_class' => 'qty-edit',
                'type'              => 'range'
            )
        );

        $this->addColumn('records_linked_to_directory',
            array(
                'header'            => Mage::helper('magedoc')->__('Linked to directory'),
                'index'             => 'records_linked_to_directory',
                'element_css_class' => 'qty-edit',
                'type'              => 'range'
            )
        );

        $this->addColumn('old_brands',
            array(
                'header'            => Mage::helper('magedoc')->__('Old brands'),
                'index'             => 'old_brands',
                'element_css_class' => 'qty-edit',
                'type'              => 'range'
            )
        );

        $this->addColumn('new_brands',
            array(
                'header'            => Mage::helper('magedoc')->__('New brands'),
                'index'             => 'new_brands',
                'element_css_class' => 'qty-edit',
                'type'              => 'range'
            )
        );

        $this->addColumn('imported_brands',
            array(
                'header'            => Mage::helper('magedoc')->__('Added to supplier map'),
                'index'             => 'imported_brands',
                'element_css_class' => 'qty-edit',
                'type'              => 'range'
            )
        );

        $this->addColumn('price_source',
            array(
                 'header'=> Mage::helper('magedoc')->__('Price file'),
                 'index' => 'price_source',
            )
        );

        $this->addColumn('messages',
            array(
                'header'=> Mage::helper('magedoc')->__('Messages'),
                'index' => 'messages',
                'frame_callback' => array($this, 'decorateMessages'),
                'width' => '100px'
            )
        );

        $this->addColumn('status_id',
            array(
                 'header'=> Mage::helper('magedoc')->__('Status'),
                 
                 'index' => 'status_id',
                 'type'  => 'options',
                 'options' => Mage::getSingleton('magedoc/source_retailer_data_import_session_status')->getAllOptions()
            )
        );

        $this->addColumn('created_at',
            array(
                 'header'=> Mage::helper('magedoc')->__('Created At'),
                 
                 'index' => 'created_at',
                 'type'  => 'datetime',
            )
        );

        $this->addColumn('updated_at',
            array(
                 'header'=> Mage::helper('magedoc')->__('Updated At'),
                 
                 'index' => 'updated_at',
                 'type'  => 'datetime',
            )
        );

        return parent::_prepareColumns();
    }

    public function decorateMessages($value, $row, $column, $isExport)
    {
        $cell = '';
        if(is_array($row->getMessages())){
            $messages = implode('<br/>', $row->getMessages());
            if(strlen($messages) > 255){
                $cell .= '<div style="overflow: scroll;height: 100px;">';
                $cell .= $messages;
                $cell .= '</div>';;
            }else{
                $cell .= $messages;
            }
        }
        return $cell;
    }
}
<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Form_Renderer_SourceAdapterOptions
    extends MageDoc_System_Block_Adminhtml_Config_Field_Array
{
    const FIELD_LIST_CONFIG_PATH = 'global/import_format_adapter_models';

    public function _prepareToRender()
    {
        $this->addColumn('option', array(
            'label' => Mage::helper('magedoc')->__('Option'),
            'style' => 'width:120px',
            'type'  => 'select',
            'renderer'  =>  'magedoc_system/adminhtml_config_field_select',
            'options'   => $this->getOptions()

        ));

        $this->addColumn('value', array(
            'label' => Mage::helper('magedoc')->__('Value'),
            'style' => 'width:90px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('magedoc')->__('Add Option');
    }

    public function getOptions()
    {
        $adapters = Mage::getConfig()
            ->getNode( static::FIELD_LIST_CONFIG_PATH )->children();
        $options = array();
        foreach ($adapters as $adapter){
            if (isset($adapter->config_options)){
                foreach ($adapter->config_options->children() as $option){
                    $options[$option->getName()] = $option->getName();
                }
            }

        }

        return $options;
    }
}

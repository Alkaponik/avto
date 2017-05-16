<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Form_Renderer_SourceAdapterMap
    extends MageDoc_System_Block_Adminhtml_Config_Field_Array
{
    const FIELD_LIST_CONFIG_PATH = 'global/magedoc/retailer_data_import_base_table/fields';

    public function _prepareToRender()
    {
        $this->addColumn('base_table_field',
            array(
                'label' => Mage::helper('magedoc')->__('Base table field'),
                'style' => 'width:120px',
                'type'  => 'select',
                'renderer'  =>  'magedoc_system/adminhtml_config_field_select',
                'values'   => $this->getBaseTableFieldList(),
            )
        );

        $this->addColumn('path',
            array(
                'label' => Mage::helper('magedoc')->__('Price field'),
                'style' => 'width:90px',
            )
        );

        $this->addColumn('default_value',
            array(
                 'label' => Mage::helper('magedoc')->__('Default value'),
                 'style' => 'width:90px',
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('magedoc')->__('Add Field');
    }

    public function getBaseTableFieldList()
    {
        $fieldList = array();
        $fields = Mage::getConfig()
            ->getNode( static::FIELD_LIST_CONFIG_PATH )->children();
        $fields = array_keys( (array) $fields );

        $fieldList['base'] = array(
            'label' => 'Base table Fields',
            'value' => array()
        );

        foreach($fields as $field) {
            $fieldList['base']['value'][] = array(
                'value' => $field,
                'label' => $field,
            );
        }

        $fieldList = array_merge($fieldList, $this->getDirectoriesExtraFields());

        return  $fieldList;
    }

    public function getDirectoriesExtraFields()
    {
        $directories = Mage::getConfig()
            ->getNode( Testimonial_MageDoc_Model_Directory::DIRECTORIES_CONFIG_XML_PATH )->asArray();
        $fieldList = array();

        foreach($directories as $code => $directory) {
            if(!empty($directory['extra_fields'])) {
                $fields = explode(',' ,$directory['extra_fields'] );
                $fieldList[$code] = array(
                    'label' => $directory['name'],
                    'value' => array()
                );
                foreach($fields as $field) {
                    $fieldList[$code]['value'][] = array(
                        'value' => $field,
                        'label' => $field,
                    );
                }
            }
        }

        return $fieldList;
    }

}

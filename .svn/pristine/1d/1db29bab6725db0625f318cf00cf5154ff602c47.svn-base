<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Form_Renderer_RetailerSchedule
    extends MageDoc_System_Block_Adminhtml_Config_Field_Array
{

    public function _prepareToRender()
    {
        $this->addColumn('schedule',
            array(
                'label' => Mage::helper('magedoc')->__('Schedule'),
                'style' => 'width:90px',
                'class' => 'required-entry',
                'value' => '5 7 * * *',
            )
        );

        $this->addColumn('source_id',
            array(
                'label' => Mage::helper('magedoc')->__('Source config'),
                'style' => 'width:120px',
                'class' => 'required-entry',
                'type'  => 'select',
                'renderer'  =>  'magedoc_system/adminhtml_config_field_select',
                'values'   => $this->getSourceFieldList(),
            )
        );

        $this->addColumn('adapter_ids',
            array(
                'label' => Mage::helper('magedoc')->__('Adapter config'),
                'style' => 'width:120px',
                'type'  => 'multiselect',
                'size' => 4,
                'class' => 'required-entry',
                'renderer'  =>  'magedoc_system/adminhtml_config_field_select',
                'values'   => $this->getAdapterFieldList(),
            )
        );

        $this->addColumn('start_new_session',
            array(
                'label' => Mage::helper('magedoc')->__('Start new session'),
                'style' => 'width:90px',
                'type'  => 'checkbox',
                'renderer'  =>  'magedoc_system/adminhtml_config_field_select',
                'checked'=> 1,
                'value' => 1,
            )
        );

        $this->addColumn('import_brands',
            array(
                'label' => Mage::helper('magedoc')->__('Import brands'),
                'style' => 'width:90px',
                'type'  => 'checkbox',
                'renderer'  =>  'magedoc_system/adminhtml_config_field_select',
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('magedoc')->__('Add Field');
    }

    public function getSourceFieldList()
    {
        $list = array();
        $retailer = Mage::registry('retailer');
        /** @var  $sourceCollection Testimonial_MageDoc_Model_Mysql4_Retailer_Import_Source_Config_Collection */
        $sourceCollection = $retailer->getImportSourceCollection();
        foreach($sourceCollection as $item){
            $list[$item->getId()] = $item->getName();
        }
        return $list;
    }

    public function getAdapterFieldList()
    {
        $list = array();
        $retailer = Mage::registry('retailer');
        /** @var  $sourceCollection Testimonial_MageDoc_Model_Mysql4_Retailer_Import_Adapter_Config_Collection */
        $adapterCollection = $retailer->getImportConfigCollection();
        foreach($adapterCollection as $item){
            $list[$item->getId()] = array('value' => $item->getId(), 'label' => $item->getName());
        }
        return $list;
    }

    public function getArrayRows()
    {
        if (null !== $this->_arrayRowsCache) {
            //return $this->_arrayRowsCache;
        }
        $result = array();
        /** @var Varien_Data_Form_Element_Abstract */
        $element = $this->getElement();
        if ($element->getValue()) {
            foreach ($element->getValue() as $item) {
                $row = array();
                $parameters = $item->getParameters();
                $row['_id'] = $item->getId();
                $row['schedule'] = $item->getSchedule();
                $row['source_id'] = isset($parameters['source_id'])? $parameters['source_id']: null;
                $row['adapter_ids'] = isset($parameters['adapter_ids'])? $parameters['adapter_ids']: null;

                $row['start_new_session'] = isset($parameters['start_new_session'])? (int)$parameters['start_new_session']: 0;
                $row['import_brands'] = isset($parameters['import_brands'])? (int)$parameters['import_brands']: 0;


                $result[$item->getId()] = new Varien_Object($row);
            }
        }
        $this->_arrayRowsCache = $result;
        return $this->_arrayRowsCache;
    }

    public function addColumn($name, $params)
    {
        $this->_columns[$name] = array_merge($params, array(
            'label'     => empty($params['label']) ? 'Column' : $params['label'],
            'size'      => empty($params['size'])  ? false    : $params['size'],
            'style'     => empty($params['style'])  ? null    : $params['style'],
            'class'     => empty($params['class'])  ? null    : $params['class'],
            'value'     => empty($params['value'])  ? null    : $params['value'],
            'renderer'  => false,
        ));
        if ((!empty($params['renderer']))) {
            if ($params['renderer'] instanceof Mage_Core_Block_Abstract){
                $this->_columns[$name]['renderer'] = $params['renderer'];
            }else{
                $this->_columns[$name]['renderer'] = $this->getLayout()->createBlock($params['renderer']);
            }
            $this->_columns[$name]['renderer']->setForm($this->getForm());
        }
        return $this;
    }

}

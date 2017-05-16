<?php
class Testimonial_MageDoc_Model_Source_Import_Update_Key
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const IMPORT_MODEL_CONFIG_PATH = 'global/price_import_update_keys';

    public function getAllOptions($withEmpty=true)
    {
        if (empty($this->_options)) {

            $this->_options = $this->toOptionArray();
        }
        if ($withEmpty) {
            array_unshift($this->_options,
                array(
                    'value'=>'',
                    'label'=>Mage::helper('core')->__('-- Please Select --')
                )
            );
        }

        return $this->_options;
    }

    public function toOptionArray()
    {
        $optionsArray = array();
        foreach (Mage::getConfig()->getNode(static::IMPORT_MODEL_CONFIG_PATH)->children() as $model) {

            $labelPath = static::IMPORT_MODEL_CONFIG_PATH . '/' . $model->getName() . '/' . 'label';
            $label = (string) Mage::getConfig()->getNode($labelPath);

            $valuePath = static::IMPORT_MODEL_CONFIG_PATH . '/' . $model->getName() . '/' . 'value';
            $value = (string)Mage::getConfig()->getNode($valuePath);

            $fieldsPath = static::IMPORT_MODEL_CONFIG_PATH . '/' . $model->getName() . '/' . 'fields';
            $fields = (string)Mage::getConfig()->getNode($fieldsPath);

            $optionsArray[$value] = array(
                'label'  => Mage::helper('magedoc')->__($label),
                'value'  => $value,
                'fields' => $fields,
            );
        }

        return $optionsArray;
    }

    public function getOptionArray()
    {
        $options = $this->getAllOptions(false);
        $optionArray = array();
        foreach($options as $option){
            $optionArray[$option['value']] = $option['label'];
        }

        return $optionArray;
    }

    public function getKeyFieldsByValue( $value )
    {
        $options = $this->getAllOptions(false);
        return $options[$value]['fields'] ? : false;
    }
}

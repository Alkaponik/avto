<?php
class Testimonial_MageDoc_Model_Source_Directory extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $values = Mage::getConfig()->getNode(Testimonial_MageDoc_Model_Directory::DIRECTORIES_CONFIG_XML_PATH);

            foreach($values->asArray() as $key => $value) {
                $this->_options[] = array(
                    'value' => $key,
                    'label' => $value['name']
                );
            }
        }

        return $this->_options;
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


}

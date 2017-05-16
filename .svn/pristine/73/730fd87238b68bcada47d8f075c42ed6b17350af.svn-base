<?php
class Testimonial_MageDoc_Model_Retailer_Data_Import_Adapter_Config extends Mage_Core_Model_Abstract
{
    const REQUIRED_FIELD_LIST_CONFIG_PATH = 'global/magedoc/retailer_data_import_base_table/required';
    const IGNORE_COLUMN_NAME = 'IGNORE';
    const UPDATE_BY_KEY_EMPTY = 0;

    protected $_retailer;
    protected $_defaultValues = null;

    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_adapter_config');
    }

    public function setRetailer(Testimonial_MageDoc_Model_Retailer $retailer)
    {
        $this->_retailer = $retailer;
        $this->setRetailerId($retailer->getId());
        return $this;
    }

    public function getRetailer()
    {
        return $this->_retailer;
    }

    protected function _beforeSave()
    {

        $this->_prepareFieldData('source_adapter_config');
        $this->_prepareFieldData('source_fields_map');
        $this->_prepareFieldData('source_fields_filters');

        if( !$this->isUpdateConfig() ) {
            $this->validateConfig();
        }

        return parent::_beforeSave();
    }

    protected function _prepareFieldData($fieldName)
    {
        $data = $this->getData($fieldName);
        unset($data['__empty']);
        if (!is_array($data)) {
            $data = array();
        }
        foreach ($data as $key => $element) {
            if (!empty($element['delete'])) {
                unset($data[$key]);
            }
        }

        $this->setData($fieldName, $data);
    }

    protected function _getRequiredFields()
    {
        $fields = Mage::getConfig()
            ->getNode(static::REQUIRED_FIELD_LIST_CONFIG_PATH)->children();

        $result = array(
            'classes' => array(),
            'fields'  => array()
        );

        foreach ($fields as $field) {
            $class = $field->getAttribute('class');
            if (!is_null($class)) {
                $result['classes'][$class][] = $field->getName();
            } else {
                $result['fields'][] = $field->getName();
            }
        }
        return $result;
    }

    public function validateConfig()
    {
        $exceptionMessage = array();
        $baseTableFields = array();

        $sourceFieldsMap = is_array($this->getSourceFieldsMap())
            ? $this->getSourceFieldsMap()
            : array();
        $sourceFieldsFilters = is_array($this->getSourceFieldsFilters())
            ? $this->getSourceFieldsFilters()
            : array();
        $sourceFields = array_merge($sourceFieldsMap, $sourceFieldsFilters);
        foreach ($sourceFields as $config) {
            $baseTableFields[] = $config['base_table_field'];
        }

        $requiredFields = $this->_getRequiredFields();

        $singleRequiredFields = $requiredFields['fields'];
        $singleRequiredFields = array_diff($singleRequiredFields, $baseTableFields);

        if (empty($singleRequiredFields)) {
            $classifiedFields = $requiredFields['classes'];

            foreach ($baseTableFields as $field) {
                foreach ($classifiedFields as $class => $requiredFields) {
                    if (in_array($field, $requiredFields)) {
                        unset($classifiedFields[$class]);
                    }
                }
            }
            if (!empty($classifiedFields)) {
                foreach ($classifiedFields as $class => $requiredFields) {
                    $exceptionMessage[] = implode(' ' . Mage::helper('magedoc')->__('or') . ' ', $requiredFields);
                }
                $exceptionMessage = implode(' ' . Mage::helper('magedoc')->__('and') . ' ', $exceptionMessage);
                $exceptionMessage = Mage::helper('magedoc')->__('Missing following field groups: %s in %s import adapter config', $exceptionMessage, $this->getName());
            }
        } else {
            $exceptionMessage
                = Mage::helper('magedoc')->__('Missing following fields: %s in %s import adapter config', implode(', ', $singleRequiredFields), $this->getName());
        }

        if (!empty($exceptionMessage)) {
            Mage::throwException($exceptionMessage);
        }

        return $this;
    }

    public function getHeaderMap()
    {
        $headerMap = $this->getSourceFieldsMap();

        foreach($headerMap as $key => $item) {
            if($item['path'] === '') {
                unset($headerMap[$key]);
            }
        }
        
        if (!is_array($headerMap)) {
            Mage::throwException(Mage::helper('magedoc')->__('Wrong source fileds map'));
        }
        usort(
            $headerMap, function ($a, $b) {
                if ($a['path'] == $b['path']) {
                    return 0;
                }
                return ($a['path'] < $b['path']) ? -1 : 1;
            }
        );

        $last = end($headerMap);
        reset($headerMap);
        $result = array();
        for ($i = 1; $i <= $last['path']; $i++) {
            $currentElement = reset($headerMap);
            if ($currentElement['path'] == $i) {
                $result[$i] = $currentElement['base_table_field'];
                array_shift($headerMap);
            }else{
                $result[$i] = self::IGNORE_COLUMN_NAME;
            }
        }

        return $result;
    }

    public function getSourceAdapterModelName()
    {
        return Mage::helper('magedoc')->getImportSourceAdapterModelName($this->getAdapterModel());
    }

    public function getParserModelName()
    {
        return Mage::helper('magedoc')->getImportParserModelName($this->getParserModel());
    }

    public function isUpdateConfig()
    {
        return $this->getUpdateByKey() != static::UPDATE_BY_KEY_EMPTY;
    }

    public function getUpdateKeyFields()
    {
        if (!$this->hasData('update_key_fields')){
            $key = Mage::getSingleton('magedoc/source_import_update_key')->getKeyFieldsByValue($this->getUpdateByKey());
            $this->setData('update_key_fields', ($key ? explode(',', $key) : array()));
        }

        return $this->getData('update_key_fields');
    }

    public function getDefaultValues( $key = null )
    {
        if(is_null($this->_defaultValues)) {
            $this->_defaultValues = array();
            foreach($this->getSourceFieldsMap() as $item) {
                if(isset($item['default_value']) && $item['default_value'] !== '') {
                    $this->_defaultValues[$item['base_table_field']] = $item['default_value'];
                }
            }
        }

        if(!is_null($key)) {
            return isset($this->_defaultValues[$key]) ? $this->_defaultValues[$key] : null;
        }

        return $this->_defaultValues;
    }
}
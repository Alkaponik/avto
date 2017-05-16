<?php

class Testimonial_System_Block_Adminhtml_Config_Field_Array extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_types = array();

    public function __construct()
    {
        parent::_construct();
        $this->setTemplate('testimonial_system/system/config/form/field/array.phtml');
    }

    /**
     * Add a column to array-grid
     *
     * @param string $name
     * @param array $params
     */
    public function addColumn($name, $params)
    {
        $this->_columns[$name] = array_merge($params, array(
            'label'     => empty($params['label']) ? 'Column' : $params['label'],
            'size'      => empty($params['size'])  ? false    : $params['size'],
            'style'     => empty($params['style'])  ? null    : $params['style'],
            'class'     => empty($params['class'])  ? null    : $params['class'],
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

    public function getArrayRows()
    {
        if (null !== $this->_arrayRowsCache) {
            //return $this->_arrayRowsCache;
        }
        $result = array();
        /** @var Varien_Data_Form_Element_Abstract */
        $element = $this->getElement();
        if ($element->getValue() && is_array($element->getValue())) {
            foreach ($element->getValue() as $rowId => $row) {
                if (!is_array($row)){
                    continue;
                }
                foreach ($row as $key => $value) {
                    $row[$key] = $this->htmlEscape($value);
                }
                $row['_id'] = $rowId;
                $result[$rowId] = new Varien_Object($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }
        $this->_arrayRowsCache = $result;
        return $this->_arrayRowsCache;
    }

    public function setAddAfter($addAfter = true)
    {
        $this->_addAfter = $addAfter;
        return $this;
    }
}

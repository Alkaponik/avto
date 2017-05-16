<?php
class Testimonial_MageDoc_Block_Adminhtml_Price_Edit_Tab_Form_Extended extends Varien_Data_Form
{
    public function setValues($values)
    {
        foreach ($this->_allElements as $element) {
            if (isset($values[$element->getId()])) {
                $element->setValue($values[$element->getId()]);
            }
            else {
                $element->setValue(null);
            }
            if($element->getType() == 'combobox'){
                $valueId = $element->getId() . '_id';
                if(isset($values[$valueId])){
                        $element->setValueId($values[$valueId]);
                }else{
                    $element->setValueId(null);
                }
            }            
        }
        return $this;
    }
}

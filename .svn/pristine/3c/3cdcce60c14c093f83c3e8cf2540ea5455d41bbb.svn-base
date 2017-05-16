<?php
class Testimonial_MageDoc_Block_Adminhtml_Form_Element_Checkbox extends Varien_Data_Form_Element_Checkbox
{
    public function getElementHtml()
    {
        $value = $this->getValue();
        if(isset($value) && $value === false){
            $this->unsetData('checked');
        }
        elseif ($checked = $this->getChecked()) {
            $this->setData('checked', true);
        }
        else {
            $this->unsetData('checked');
        }
        return parent::getElementHtml();
    }
}

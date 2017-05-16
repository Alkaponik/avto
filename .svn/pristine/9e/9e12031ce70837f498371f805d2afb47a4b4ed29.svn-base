<?php
class Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Column_Renderer_Checkbox
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox
{
    public function render(Varien_Object $row)
    {
        $values = $this->getColumn()->getValues();
        $value  = $row->getData($this->getColumn()->getIndex());
        if (is_array($values)) {
            $checked = in_array($value, $values) ? ' checked="checked"' : '';
        }
        else {
            $checked = ($value === $this->getColumn()->getValues()) ? ' checked="checked"' : '';
        }

        $disabledValues = $this->getColumn()->getDisabledValues();
        if (is_array($disabledValues)) {
            $disabled = in_array($value, $disabledValues) ? ' disabled="disabled"' : '';
        }
        else {
            $disabled = ($value === $this->getColumn()->getDisabledValue()) ? ' disabled="disabled"' : '';
        }

        $this->setDisabled($disabled);

        $name =  $this->getElementName($row);
        return $this->_getCheckboxHtml($values, $checked, $name);
    }

    public function getElementName($row)
    {
        if($this->getColumn()->getName() === null){
            $name = "{$this->getColumn()->getGrid()->getId()}[{$row->getId()}][{$this->getColumn()->getIndex()}]";
            return $name;
        }

        return $this->getColumn()->getName();
    }


    protected function _getCheckboxHtml($values, $checked, $name = '' )
    {
        $html = '<input type="checkbox" ';
        $html .= 'name="' . $name . '" ';
        $html .= 'value="' . $this->escapeHtml($values) . '" ';
        $html .= 'class="'. ($this->getColumn()->getInlineCss() ? $this->getColumn()->getInlineCss() : 'checkbox') .'"';
        $html .= $checked . $this->getDisabled() . '/>';
        return $html;
    }
}

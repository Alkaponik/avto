<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Column_Renderer_Options_Select 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{   
    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getOptions();        
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            if (isset($options[$value])) {
                return $this->getElementHtml($row, $value);
            } elseif (in_array($value, $options)) {
                return $this->getElementHtml($row, $value);
            }
            return $this->getElementHtml($row, $value);
        }
    }
    
    public function getElementName($row)
    {
        if($this->getColumn()->getName() === null){
           $name = "{$this->getColumn()->getGrid()->getId()}[{$row->getId()}][{$this->getColumn()->getIndex()}]";
           return $name;
        }
        return $this->getColumn()->getName();
    }

    
    public function getElementHtml($row, $elementValue)
    {
        $style = $this->getColumn()->getElementStyle()
            ? $this->getColumn()->getElementStyle()
            : "width:100%;";
        $html = "<select style=\"{$style}\" class=\"{$this->getColumn()->getElementCssClass()}\" name=\"{$this->getElementName($row)}\">";
        $options = $this->getColumn()->getInternalOptions();
        if (!is_array($options)){
            $options = $this->getColumn()->getOptions();
        }
        foreach($options as $key => $value){
            if(is_array($value)){
                $key = $value['value'];
                $value = $value['label'];
            }
            if($elementValue == $key && !is_null($elementValue)){
                $html .= "<option value=\"{$key}\" selected=\"selected\">{$value}</option>";                
            }else{
                $html .= "<option value=\"{$key}\">{$value}</option>";
            }
        }
        $html .= "</select>";
        return $html;
    }
}

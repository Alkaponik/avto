<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Column_Renderer_Text_Input
        extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        return $this->getElementHtml($row);
    }
    
    public function getElementName($row)
    {
        if($this->getColumn()->getName() === null){
           $name = "{$this->getColumn()->getGrid()->getId()}[{$row->getId()}][{$this->getColumn()->getIndex()}]";
           return $name;
        }
        return $this->getColumn()->getName();
    }
    
    public function getElementHtml($row)
    {
        $html = "<input class=\"{$this->getColumn()->getElementCssClass()}\""
            . "style=\"text-align:right;\" type=\"text\""
            . "name=\"{$this->getElementName($row)}\""
            . "value=\"{$this->_getValue($row)}\">";
            
        return $html;
    }
}

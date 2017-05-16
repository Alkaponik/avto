<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Column_Renderer_Text_Date
        extends Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Column_Renderer_Text_Input
{    
    public function getElementHtml($row)
    {
        $html = "<input class=\"{$this->getColumn()->getElementCssClass()} date\" style=\"text-align:right;\" type=\"text\""
            . "name=\"{$this->getElementName($row)}\""
            . "value=\"{$this->_getValue($row)}\">";
            
        return $html;
    }
}

<?php

class Testimonial_System_Block_Adminhtml_Widget_Grid_Column_Filter_Multiselect extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    const DEFAULT_SIZE = 3;

    /**
     * Render an option with selected value
     *
     * @param array $option
     * @param string $value
     * @return string
     */
    protected function _renderOption($option, $value)
    {
        $selected = in_array($option['value'], $value) ? ' selected="selected"' : '';
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.'>'.$this->escapeHtml($option['label']).'</option>';
    }

    public function getHtml()
    {
        $size = $this->getColumn()->getSize()
            ? $this->getColumn()->getSize()
            : self::DEFAULT_SIZE;
        $html = '<select name="'.$this->_getHtmlName().'" id="'.$this->_getHtmlId().'" class="no-changes" multiple="multiple" size="'.$size.'">';
        $value = $this->getValue();
        foreach ($this->_getOptions() as $option){
            if (is_array($option['value'])) {
                $html .= '<optgroup label="' . $this->escapeHtml($option['label']) . '">';
                foreach ($option['value'] as $subOption) {
                    $html .= $this->_renderOption($subOption, $value);
                }
                $html .= '</optgroup>';
            } else {
                $html .= $this->_renderOption($option, $value);
            }
        }
        $html.='</select>';
        return $html;
    }

    public function getCondition()
    {
        if (count($this->getValue()) == 0) {
            return null;
        }
        return array('in' => $this->getValue());
    }

    public function getValue()
    {
        return strlen($this->getData('value'))
            ? explode(',', $this->getData('value'))
            : array();
    }
}

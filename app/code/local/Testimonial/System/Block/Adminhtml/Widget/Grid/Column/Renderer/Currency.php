<?php

class Testimonial_System_Block_Adminhtml_Widget_Grid_Column_Renderer_Currency extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Currency
{

    /**
     * Renders column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        if (($data = (string)$row->getData($this->getColumn()->getIndex()))
                && (!$this->getColumn()->getHideZeros() || $data != 0)) {
            $currency_code = $this->_getCurrencyCode($row);

            if (!$currency_code) {
                return $data;
            }

            $data = floatval($data) * $this->_getRate($row);
            $sign = (bool)(int)$this->getColumn()->getShowNumberSign() && ($data > 0) ? '+' : '';
            $data = sprintf("%f", $data);
            $data = Mage::app()->getLocale()->currency($currency_code)->toCurrency($data);
            return $sign . $data;
        }
        return $this->getColumn()->getDefault();
    }
}

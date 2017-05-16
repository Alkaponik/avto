<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Phoenix
 * @package    Phoenix_LayeredNav
 * @copyright  Copyright (c) 2011 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */
class Phoenix_LayeredNav_Block_Adminhtml_RequestVarAlias extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = '<div id="requestvar_alias_template" style="display:none">';
        $html .= $this->_getRowTemplateHtml();
        $html .= '</div>';

        $html .= '<ul id="requestvar_alias_container">';
        if ($this->_getValue('request_var')) {
            foreach ($this->_getValue('request_var') as $i=>$f) {
                if ($i) {
                    $html .= $this->_getRowTemplateHtml($i);
                }
            }
        }
        $html .= '</ul>';
        $html .= $this->_getAddRowButtonHtml('requestvar_alias_container',
            'requestvar_alias_template', $this->__('Add Alias'));

        return $html;
    }

    protected function _getRowTemplateHtml($i=0)
    {
        $html = '<li>';
        $html .= '<div style="margin:5px 0 15px;">';
        $html .= '<label>'.$this->__('Request Var:').'</label> ';
        $html .= '<input type="text" name="'.$this->getElement()->getName().'[request_var][]" value="' . $this->_getValue('request_var/'.$i) . '" style="width:160px;float: right;" />';
        $html .= '</div>';

        $html .= '<div style="margin:5px 0 15px;">';
        $html .= '<label>'.$this->__('Product Attribute:').'</label> ';
        $html .= '<select name="'.$this->getElement()->getName().'[attribute_code][]" '.$this->_getDisabled().' style="width:160px;float: right;">';
        $html .= '<option value="">'.$this->__('No Associated Attribute').'</option>';
        foreach ($this->_getFilterableAttributes() as $option) {
            $html .= '<option value="'.$option['value'].'" '.$this->_getSelected('attribute_code/'.$i, $option['value']).' >'.$option['label'].'</option>';
        }
        $html .= '</select>';
        $html .= '</div>';

        $html .= '<div style="margin:5px 0 15px;">';
        $html .= '<label>'.$this->__('Alias:').'</label> ';
        $html .= '<input type="text" name="'.$this->getElement()->getName().'[alias][]" value="' . $this->_getValue('alias/'.$i) . '" style="margin:0 15px 0 5px;width:120px" />';
        $html .= $this->_getRemoveRowButtonHtml();
        $html .= '</div>';
        $html .= '</li>';

        return $html;
    }    

    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }

    protected function _getValue($key)
    {
        return $this->getElement()->getData('value/'.$key);
    }

    protected function _getSelected($key, $value)
    {
        return $this->getElement()->getData('value/'.$key)==$value ? 'selected="selected"' : '';
    }

    protected function _getAddRowButtonHtml($container, $template, $title='Add')
    {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('add '.$this->_getDisabled())
                    ->setLabel($this->__($title))
                    ->setOnClick("Element.insert($('".$container."'), {bottom: $('".$template."').innerHTML})")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }

    protected function _getRemoveRowButtonHtml($selector='li', $title='Delete')
    {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('delete v-middle '.$this->_getDisabled())
                    ->setLabel($this->__($title))
                    ->setOnClick("Element.remove($(this).up('".$selector."'))")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }

    protected function _getElements()
    {
        return Mage::app()->getStores();
    }

    protected function _getFilterableAttributes()
    {
        return Mage::getSingleton('phoenix_layerednav/source_filterableAttribute')->toOptionArray(false);
    }
}

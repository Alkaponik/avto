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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Select grid column filter
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Testimonial_MageDoc_Block_Adminhtml_Widget_Grid_Column_Filter_Combobox extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{

    public function __construct()
    {
        parent::__construct();
        $this->_renderer = Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_combobox');
        $this->_renderer->setTemplate('magedoc/widget/grid/filter/combobox.phtml');
        return $this;
    }

    public function setColumn($column)
    {
        parent::setColumn($column);
        $this->_renderer->setData($column->getData());
        $this->_renderer->setValues($column->getOptions());
        return $this;
    }

    protected function _getOptions()
    {
        $emptyOption = array('value' => null, 'label' => '');

        $optionGroups = $this->getColumn()->getOptionGroups();
        if ($optionGroups) {
            array_unshift($optionGroups, $emptyOption);
            return $optionGroups;
        }

        $colOptions = $this->getColumn()->getOptions();
        if (!empty($colOptions) && is_array($colOptions) ) {
            $options = array($emptyOption);
            foreach ($colOptions as $value => $label) {
                $options[] = array('value' => $value, 'label' => $label);
            }
            return $options;
        }
        return array();
    }

    /**
     * Render an option with selected value
     *
     * @param array $option
     * @param string $value
     * @return string
     */
    protected function _renderOption($option, $value)
    {
        $selected = (($option['value'] == $value && (!is_null($value))) ? ' selected="selected"' : '' );
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.'>'.$this->escapeHtml($option['label']).'</option>';
    }

    public function getHtml()
    {
        $this->_renderer->setId($this->_getHtmlId());
        $this->_renderer->setName($this->_getHtmlName());
        $this->_renderer->setValue($this->getValue());
        return $this->_renderer->toHtml();
    }

    public function getCondition()
    {
        if (is_null($this->getValue())) {
            return null;
        }
        return array('eq' => $this->getValue());
    }

}

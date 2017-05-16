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
 * Adminhtml order totals block
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Testimonial_MageDoc_Block_Adminhtml_Order_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals//Mage_Adminhtml_Block_Sales_Order_Abstract
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $totals = $this->_totals;
        $this->_totals = array();

        $this->_totals['cost'] = new Varien_Object(array(
            'code'      => 'cost',
            'strong'    => false,
            'value'     => $this->getSource()->getCost(),
            'base_value'=> $this->getSource()->getBaseCost(),
            'label'     => $this->helper('magedoc')->__('Total Cost'),
            'area'      => ''
        ));
        $this->_totals['margin'] = new Varien_Object(array(
            'code'      => 'margin',
            'strong'    => false,
            'value'     => $this->getSource()->getMargin(),
            'base_value'=> $this->getSource()->getBaseMargin(),
            'label'     => $this->helper('magedoc')->__('Total Margin'),
            'area'      => ''
        ));

        $this->_totals = array_merge($this->_totals, $totals);

        return $this;
    }
}

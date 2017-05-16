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
 * Adminhtml sales report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Testimonial_MageDoc_Block_Adminhtml_Report_Sales_Sales_Grid extends Mage_Adminhtml_Block_Report_Sales_Sales_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
    }

    protected function _addCustomFilter($collection, $filterData)
    {
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        $groupBy = array();

        $orderBy = array('period');
        if(!Mage::getSingleton('admin/session')->isAllowed('report/salesroot/sales/view_all')){
            $collection->addFieldToFilter('manager_id', $adminUser->getId());
        }elseif ($managerId = $filterData->getData('manager_id')){
            $collection->addFieldToFilter('manager_id', $managerId);
        }elseif ($filterData->getData('show_manager')){
            if (!$collection->isTotals() && !$collection->isSubTotals()) {
                //$collection->getSelect()->group('manager_id');
                $groupBy[] = 'manager_id';
                $orderBy[] = 'manager_id';

            }
        }

        if ($supplyStatus = $filterData->getData('supply_status')) {
            $collection->addFieldToFilter('supply_status', array('in' => explode(',',$supplyStatus[0])));
        }
        if ($filterData->getData('show_supply_status')){
            if (!$collection->isTotals() && !$collection->isSubTotals()) {
                //$collection->getSelect()->group('supply_status');
                $groupBy[] = 'supply_status';
                $orderBy[] = 'supply_status';
            }
        }
        if ($filterData->getData('show_shipping_method')){
            if (!$collection->isTotals() && !$collection->isSubTotals()) {
                $groupBy[] = 'shipping_method';
                $orderBy[] = 'shipping_method';
            }
        }
        if ($filterData->getData('show_payment_method')){
            if (!$collection->isTotals() && !$collection->isSubTotals()) {
                $groupBy[] = 'payment_method';
                $orderBy[] = 'payment_method';
            }
        }
        if (count($orderBy) > 1){
            $collection->getSelect()->order($orderBy);
        }
        if (count($groupBy)){
            $collection->getSelect()->group($groupBy);
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumnAfter('manager', array(
        'header' => Mage::helper('magedoc')->__('Manager'),
        'index' => 'manager_id',
        'type'  => 'options',
        'width' => '70px',
        'options' => Mage::getSingleton('magedoc/source_orderManager')->getOptionArray(),
        'visibility_filter' => array('show_manager'),
        ), 'period');

        $this->addColumnAfter('order_supply_status', array(
            'header' => Mage::helper('magedoc')->__('Supply Status'),
            'index' => 'supply_status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_order_supply_status')->getOptionArray(),
            'visibility_filter' => array('show_supply_status'),
        ), 'manager');

        $this->addColumnAfter('order_shipping_method', array(
            'header' => Mage::helper('magedoc')->__('Shipping Method'),
            'index' => 'shipping_method',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_availableShippingCarriers')->getOptionArray(),
            'show_missing_option_values' => true,
            'visibility_filter' => array('show_shipping_method'),
        ), 'manager');

        $this->addColumnAfter('order_payment_method', array(
            'header' => Mage::helper('magedoc')->__('Payment Method'),
            'index' => 'payment_method',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::helper('magedoc')->getPaymentMethodsHash(),
            'show_missing_option_values' => true,
            'visibility_filter' => array('show_payment_method'),
        ), 'manager');

        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumnAfter('avg_order_items', array(
            'header'        => Mage::helper('sales')->__('Avg Order Items'),
            //'type'          => 'number',
            'index'         => 'avg_order_items',
            'total'         => 'avg',
            'sortable'      => false,
            'column_css_class' => 'a-right'
        ), 'total_qty_invoiced');

        $this->addColumnAfter('avg_order_total', array(
            'header'        => Mage::helper('sales')->__('Avg Order Total'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'avg_order_total',
            'total'         => 'avg',
            'sortable'      => false,
            'rate'          => $rate,
        ), 'avg_order_items');

        $this->addColumnAfter('avg_margin_percent', array(
            'header'        => Mage::helper('sales')->__('Avg Margin %'),
            //'type'          => 'number',
            'index'         => 'avg_margin_percent',
            'total'         => 'avg',
            'sortable'      => false,
            'column_css_class' => 'a-right'
        ), 'avg_order_total');

        return parent::_prepareColumns();
    }

    protected function __preparePage()
    {
        parent::_preparePage();
        if (!$this->_isExport && ($collection = $this->getCollection()->getResourceCollection())){

        }
    }
}

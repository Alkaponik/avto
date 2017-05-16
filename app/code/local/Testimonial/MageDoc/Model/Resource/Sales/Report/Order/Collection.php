<?php

class Testimonial_MageDoc_Model_Resource_Sales_Report_Order_Collection extends Mage_Sales_Model_Resource_Report_Order_Collection
{
    /**
     * Get selected columns
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();
        $adapter = $this->getConnection();
        if ('week' == $this->_period) {
            $this->_periodFormat = "{$adapter->getDateFormatSql('MIN(period)', '%Y-%m-%d')}";
        } elseif ('month' == $this->_period) {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $adapter->getDateExtractSql('period', Varien_Db_Adapter_Interface::INTERVAL_YEAR);
        } else {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals()) {
            $this->_selectedColumns['period'] = $this->_periodFormat;
            $this->_selectedColumns['manager_id'] = 'manager_id';
            $this->_selectedColumns['supply_status'] = 'supply_status';
            $this->_selectedColumns['shipping_method'] = 'shipping_method';
            $this->_selectedColumns['payment_method'] = 'payment_method';
            $this->_selectedColumns['avg_order_items'] = 'FORMAT(SUM(total_qty_ordered)/SUM(orders_count), 2)';
            $this->_selectedColumns['avg_order_total'] = 'SUM(total_income_amount)/SUM(orders_count)';
            //$this->_selectedColumns['avg_margin_percent'] = 'FORMAT(AVG(IF(total_income_amount, (total_profit_amount + total_income_amount - total_paid_amount)/total_revenue_amount, 0 ))*100, 2)';
            $this->_selectedColumns['avg_margin_percent'] = 'FORMAT(SUM(total_profit_amount)/SUM(total_revenue_amount - total_profit_amount)*100, 2)';
        }

        if ($this->isTotals()) {
            $this->_selectedColumns['avg_order_items'] = 'FORMAT(AVG(total_qty_ordered/orders_count), 2)';
            $this->_selectedColumns['avg_order_total'] = 'AVG(total_income_amount/orders_count)';
            $this->_selectedColumns['avg_margin_percent'] = 'FORMAT(AVG(IF(total_income_amount, (total_profit_amount + total_income_amount - total_paid_amount)/total_revenue_amount, 0 ))*100, 2)';
        }

        return $this->_selectedColumns;
    }

    /**
     * Add selected data
     *
     * @return Mage_Sales_Model_Resource_Report_Order_Collection
     */
    protected function _initSelect()
    {
        $this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns());
        if (!$this->isTotals()) {
            if ('week' == $this->_period){
                $this->getSelect()->group(new Zend_Db_Expr('YEARWEEK(period, 1)'));
            }else{
                $this->getSelect()->group($this->_periodFormat);
            }
        }
        return $this;
    }
}

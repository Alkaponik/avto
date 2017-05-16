<?php

class Testimonial_MageDoc_Model_Resource_Sales_Report_Order_Createdat extends Mage_Sales_Model_Resource_Report_Order_Createdat
{
    /**
     * Aggregate Orders data by custom field
     *
     * @throws Exception
     * @param string $aggregationField
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Sales_Model_Resource_Report_Order_Createdat
     */
    protected function _aggregateByField($aggregationField, $from, $to)
    {
        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from = $this->_dateToUtc($from);
        $to   = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);
        $adapter = $this->_getWriteAdapter();

        $adapter->beginTransaction();
        try {

            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect(
                    $this->getTable('sales/order'),
                    'created_at', 'updated_at', $from, $to
                );
            } else {
                $subSelect = null;
            }
            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);

            $periodExpr = $adapter->getDatePartSql($this->getStoreTZOffsetQuery(
                array('o' => $this->getTable('sales/order')),
                'o.' . $aggregationField,
                $from, $to
            ));
            // Columns list
            $columns = array(
                // convert dates from UTC to current admin timezone
                'period'                         => $periodExpr,
                'store_id'                       => 'o.store_id',
                'manager_id'                     => 'o.manager_id',
                'order_status'                   => 'o.status',
                'supply_status'                  => 'o.supply_status',
                'shipping_method'                => 'og.shipping_carrier',
                'payment_method'                 => 'p.method',
                'orders_count'                   => new Zend_Db_Expr('COUNT(o.entity_id)'),
                'total_qty_ordered'              => new Zend_Db_Expr('SUM(IFNULL(oi.total_qty_ordered, 0) + IFNULL(oin.total_qty_ordered, 0))'),
                'total_qty_invoiced'             => new Zend_Db_Expr('SUM(IFNULL(oi.total_qty_invoiced, 0) + IFNULL(oin.total_qty_invoiced, 0))'),
                'total_income_amount'            => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('o.base_grand_total', 0),
                        $adapter->getIfNullSql('o.base_total_canceled',0),
                        $adapter->getIfNullSql('o.base_to_global_rate',0)
                    )
                ),
                'total_revenue_amount'           => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s - %s - (%s - %s - %s)) * %s)',
                        $adapter->getIfNullSql('o.base_total_invoiced', 0),
                        $adapter->getIfNullSql('o.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('o.base_shipping_invoiced', 0),
                        $adapter->getIfNullSql('o.base_total_refunded', 0),
                        $adapter->getIfNullSql('o.base_tax_refunded', 0),
                        $adapter->getIfNullSql('o.base_shipping_refunded', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_profit_amount'            => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s - %s - %s - %s) * %s)',
                        $adapter->getIfNullSql('o.base_total_paid', 0),
                        $adapter->getIfNullSql('o.base_total_refunded', 0),
                        $adapter->getIfNullSql('o.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('o.base_shipping_invoiced', 0),
                        $adapter->getIfNullSql('o.base_total_invoiced_cost', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_invoiced_amount'          => new Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('o.base_total_invoiced', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_canceled_amount'          => new Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('o.base_total_canceled', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_paid_amount'              => new Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('o.base_total_paid', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_refunded_amount'          => new Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('o.base_total_refunded', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount'               => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('o.base_tax_amount', 0),
                        $adapter->getIfNullSql('o.base_tax_canceled', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount_actual'        => new Zend_Db_Expr(
                    sprintf('SUM((%s -%s) * %s)',
                        $adapter->getIfNullSql('o.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('o.base_tax_refunded', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount'          => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('o.base_shipping_amount', 0),
                        $adapter->getIfNullSql('o.base_shipping_canceled', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount_actual'   => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('o.base_shipping_invoiced', 0),
                        $adapter->getIfNullSql('o.base_shipping_refunded', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_discount_amount'          => new Zend_Db_Expr(
                    sprintf('SUM((ABS(%s) - %s) * %s)',
                        $adapter->getIfNullSql('o.base_discount_amount', 0),
                        $adapter->getIfNullSql('o.base_discount_canceled', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_discount_amount_actual'   => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('o.base_discount_invoiced', 0),
                        $adapter->getIfNullSql('o.base_discount_refunded', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                )
            );

            $select          = $adapter->select();
            $selectOrderItem = $adapter->select();
            $selectOrderInquiry = $adapter->select();

            $qtyCanceledExpr = $adapter->getIfNullSql('qty_canceled', 0);
            $cols            = array(
                'order_id'           => 'order_id',
                'total_qty_ordered'  => new Zend_Db_Expr("SUM(qty_ordered - {$qtyCanceledExpr})"),
                'total_qty_invoiced' => new Zend_Db_Expr('SUM(qty_invoiced)'),
            );
            $selectOrderItem->from($this->getTable('sales/order_item'), $cols)
                ->where('parent_item_id IS NULL')
                ->group('order_id');

            $selectOrderInquiry->from($this->getTable('magedoc/order_inquiry'), $cols)
                ->group('order_id');

            $select->from(array('o' => $this->getTable('sales/order')), $columns)
                ->joinLeft(array('oi' => $selectOrderItem), 'oi.order_id = o.entity_id', array())
                ->joinLeft(array('oin' => $selectOrderInquiry), 'oin.order_id = o.entity_id AND oi.order_id IS NULL', array())
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ));
            $select->joinInner(array('p' => $this->getTable('sales/order_payment')), 'o.entity_id = p.parent_id', array());
            $select->joinInner(array('og' => $this->getTable('sales/order_grid')), 'o.entity_id = og.entity_id', array());

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array(
                $periodExpr,
                'o.store_id',
                'o.status',
                'o.supply_status',
                'o.manager_id',
                'og.shipping_carrier',
                'p.method'
            ));

            $adapter->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));

            // setup all columns to select SUM() except period, store_id and order_status
            // and manager_id
            foreach ($columns as $k => $v) {
                $columns[$k] = new Zend_Db_Expr('SUM(' . $k . ')');
            }
            $columns['period']         = 'period';
            $columns['store_id']       = new Zend_Db_Expr(Mage_Core_Model_App::ADMIN_STORE_ID);
            $columns['order_status']   = 'order_status';
            $columns['supply_status']  = 'supply_status';
            $columns['manager_id']     = 'manager_id';
            $columns['shipping_method']= 'shipping_method';
            $columns['payment_method'] = 'payment_method';

            $select->reset();
            $select->from($this->getMainTable(), $columns)
                ->where('store_id <> 0');

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array(
                'period',
                'order_status',
                'supply_status',
                'manager_id',
                'shipping_method',
                'payment_method'
            ));
            $adapter->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }
        return $this;
    }
}

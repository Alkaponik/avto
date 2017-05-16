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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote_address'), 'cost', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Cost',
    'scale'     => 4,
    'precision' => 12,
));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote_address'), 'base_cost', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Base Cost',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote_address'), 'margin', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Margin',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote_address'), 'base_margin', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Base Margin',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote'), 'cost', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Cost',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote'), 'base_cost', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Base Cost',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote'), 'margin', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Margin',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote'), 'base_margin', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Base Margin',
    'scale'     => 4,
    'precision' => 12,
));



$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'cost', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Cost',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'base_cost', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Base Cost',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'margin', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Margin',
    'scale'     => 4,
    'precision' => 12,
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'base_margin', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'comment' => 'Base Margin',
    'scale'     => 4,
    'precision' => 12,
));

/**
 * Create table 'magedoc/creditmemo_inquiry'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('magedoc/creditmemo_inquiry'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Parent Id')
    ->addColumn('base_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Base Price')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Tax Amount')
    ->addColumn('base_row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Base Row Total')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Discount Amount')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Row Total')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Base Discount Amount')
    ->addColumn('price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Price Incl Tax')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Base Tax Amount')
    ->addColumn('base_price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Base Price Incl Tax')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Qty')
    ->addColumn('base_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Base Cost')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Price')
    ->addColumn('base_row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Base Row Total Incl Tax')
    ->addColumn('row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Row Total Incl Tax')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
), 'Product Id')
    ->addColumn('order_inquiry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
), 'Order Item Id')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
), 'Additional Data')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
), 'Description')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
), 'Sku')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
), 'Name')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
), 'Base Hidden Tax Amount')
    ->addIndex($installer->getIdxName('magedoc/creditmemo_inquiry', array('parent_id')),
    array('parent_id'))
    ->addForeignKey($installer->getFkName('magedoc/creditmemo_inquiry', 'parent_id', 'sales/creditmemo', 'entity_id'),
    'parent_id', $installer->getTable('sales/creditmemo'), 'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Creditmemo Inquiry');
$installer->getConnection()->createTable($table);
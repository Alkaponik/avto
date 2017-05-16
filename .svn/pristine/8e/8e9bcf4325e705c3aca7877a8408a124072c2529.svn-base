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

$installer->startSetup();

/**
 * Create table 'magedoc/retailer_config_supply'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('magedoc/retailer_config_supply'))
    ->addColumn('retailer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Retailer Id')
    ->addColumn('delivery_type', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
), 'Delivery Type')
    ->addColumn('delivery_term_days', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0'
), 'Is Shipping Available')
    ->addColumn('order_hours_end', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
), 'Order Hours End')
    ->addColumn('express_delivery_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
    'nullable'  => false,
    'default'   => '0'
), 'Express Delivery Cost')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
), 'Description')
    ->addForeignKey($installer->getFkName('magedoc/retailer_config_supply', 'retailer_id', 'magedoc/retailer', 'retailer_id'),
    'retailer_id', $installer->getTable('magedoc/retailer'), 'retailer_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('MageDoc Retailer Supply Config');
$installer->getConnection()->createTable($table);

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_status_history'), 'manager_id', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned'  => true,
    'nullable'  => false,
    'comment'   => 'Order Manager Id'
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_status_history'), 'manager_name', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'  => 255,
    'comment'   => 'Order Manager Name'
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'manager_name', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'  => 255,
    'comment'   => 'Order Manager Name'
));

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'last_manager_name', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'  => 255,
    'comment'   => 'Last Order Manager Name'
));

$entities = array('invoice', 'shipment', 'creditmemo');

foreach ($entities as $entity) {

    $installer->getConnection()
        ->addColumn($installer->getTable('sales/'.$entity), 'manager_id', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned' => true,
        'nullable' => false,
        'comment'   => ucfirst($entity).' Manager Id'
    ));

    $installer->getConnection()
        ->addColumn($installer->getTable('sales/'.$entity), 'last_manager_id', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned' => true,
        'nullable' => false,
        'comment'   => ucfirst($entity).'Last Manager Id'
    ));

    $installer->getConnection()
        ->addColumn($installer->getTable('sales/'.$entity), 'manager_name', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'comment'   => ucfirst($entity).' Manager Name'
    ));

    $installer->getConnection()
        ->addColumn($installer->getTable('sales/'.$entity), 'last_manager_name', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'comment'   => ucfirst($entity).'Last Manager Name'
    ));
}

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_aggregated_created'), 'manager_id',
    'INTEGER(10) UNSIGNED DEFAULT NULL AFTER `store_id`');

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_aggregated_updated'), 'manager_id',
    'INTEGER(10) UNSIGNED DEFAULT NULL AFTER `store_id`');

$installer->endSetup();
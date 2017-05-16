<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

/* @var $installer Oro_Dashboard_Model_Resource_Setup */
$installer = $this;

/**
 * Create table 'oro_dashboard/dashboard'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('oro_dashboard/dashboard'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity' => true,
    'unsigned' => true,
    'nullable' => false,
    'primary' => true,
), 'Dashboard ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    'nullable' => false,
    'default' => '',
), 'Dashboard Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
    'nullable' => false,
    'default' => '',
), 'Dashboard Description')
    ->addColumn('created_by', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'Author User ID')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(), 'Created At')
    ->addColumn('layout', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    'nullable' => false,
    'default' => '',
), 'Dashboard Layout')
    ->addColumn('default_store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'nullable' => true,
), 'Default Dashboard Store')
    ->addIndex($installer->getIdxName('oro_dashboard/dashboard', array('created_by')),
    array('created_by'))
    ->addForeignKey(
    $installer->getFkName(
        ('oro_dashboard/dashboard'),
        'created_by',
        'admin/user',
        'user_id'
    ),
    'created_by', $installer->getTable('admin/user'), 'user_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
    $installer->getFkName(
        ('oro_dashboard/dashboard'),
        'default_store_id',
        'core/store',
        'store_id'
    ),'default_store_id', $installer->getTable('core/store'), 'store_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Oro Dashboard Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'oro_dashboard/permissions_role'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('oro_dashboard/permissions_role'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity' => true,
    'unsigned' => true,
    'nullable' => false,
    'primary' => true,
), 'Role Permission ID')
    ->addColumn('dashboard_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'Dashboard ID')
    ->addColumn('user_role_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'User Role ID')
    ->addColumn('view', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'View Dashboard Flag')
    ->addColumn('edit', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'Edit Dashboard Flag')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'Is Default For Role')
    ->addIndex($installer->getIdxName('oro_dashboard/permissions_role', array('user_role_id')),
    array('user_role_id'))
    ->addIndex($installer->getIdxName('oro_dashboard/permissions_role', array('dashboard_id', 'user_role_id')),
    array('dashboard_id', 'user_role_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
    $installer->getFkName(
        ('oro_dashboard/permissions_role'),
        'user_role_id',
        'admin/role',
        'role_id'
    ),
    'user_role_id', $installer->getTable('admin/role'), 'role_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
    $installer->getFkName(
        ('oro_dashboard/permissions_role'),
        'dashboard_id',
        'oro_dashboard/dashboard',
        'id'
    ),
    'dashboard_id', $installer->getTable('oro_dashboard/dashboard'), 'id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Oro Permissions Role Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'oro_dashboard/permissions_user'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('oro_dashboard/permissions_user'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity' => true,
    'unsigned' => true,
    'nullable' => false,
    'primary' => true,
), 'User Permission ID')
    ->addColumn('dashboard_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'Dashboard ID')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'User ID')
    ->addColumn('view', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'View Dashboard Flag')
    ->addColumn('edit', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'Edit Dashboard Flag')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'Is Default For User')
    ->addIndex($installer->getIdxName('oro_dashboard/permissions_user', array('user_id')),
    array('user_id'))
    ->addIndex($installer->getIdxName('oro_dashboard/permissions_user', array('dashboard_id', 'user_id')),
    array('dashboard_id', 'user_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
    $installer->getFkName(
        ('oro_dashboard/permissions_user'),
        'user_id',
        'admin/user',
        'user_id'
    ),
    'user_id', $installer->getTable('admin/user'), 'user_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
    $installer->getFkName(
        ('oro_dashboard/permissions_user'),
        'dashboard_id',
        'oro_dashboard/dashboard',
        'id'
    ),
    'dashboard_id', $installer->getTable('oro_dashboard/dashboard'), 'id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Oro Permissions User Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'oro_dashboard/widget'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('oro_dashboard/widget'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity' => true,
    'unsigned' => true,
    'nullable' => false,
    'primary' => true,
), 'Widget ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    'nullable' => false,
    'default' => '',
), 'Widget Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
    'nullable' => false,
    'default' => '',
), 'Widget Description')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(), 'Created At')
    ->addColumn('widget_config', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
    'nullable' => false,
    'default' => '',
), 'Widget Config')
    ->setComment('Oro Dashboard Widgets Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'oro_dashboard/widget_relation'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('oro_dashboard/widget_relation'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity' => true,
    'unsigned' => true,
    'nullable' => false,
    'primary' => true,
), 'Widget Relation ID')
    ->addColumn('dashboard_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'Dashboard ID')
    ->addColumn('widget_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
), 'Widget ID')
    ->addColumn('position_column', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default' => 1,
        'comment' => 'Widget Column'
    ))
    ->addColumn('position_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'nullable' => false,
    'default' => 0,
    'comment' => 'Widget Order Position'
    ))
    ->addIndex($installer->getIdxName('oro_dashboard/widget_relation', array('dashboard_id', 'widget_id')),
    array('dashboard_id', 'widget_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
    $installer->getFkName(
        ('oro_dashboard/widget_relation'),
        'dashboard_id',
        'oro_dashboard/dashboard',
        'dashboard_id'
    ),
    'dashboard_id', $installer->getTable('oro_dashboard/dashboard'), 'id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
    $installer->getFkName(
        ('oro_dashboard/widget_relation'),
        'widget_id',
        'oro_dashboard/widget',
        'id'
    ),
    'widget_id', $installer->getTable('oro_dashboard/widget'), 'id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Oro Dashboard Widget Relation Table');
$installer->getConnection()->createTable($table);

$installer->setConfigData(Oro_Dashboard_Helper_Data::XML_CURRENT_STORE_TIMEZONE, $installer->getStoreTimezone());

$installer->endSetup();

<?php
/**
 * NOTICE OF LICENSE
 *
 *
 * @package     Serpini_Sqlreport
 * @copyright   Copyright (c) 2013 Serpini (http://www.serpini.es/sqlreport)
 */

Mage::log('Running Serpini Sqlreport DB upgrade ');

// Start Sqlreport Setup
$installer = $this;
/* @var $installer Serpini_Sqlreport_Model_Mysql4_Setup */

$installer->startSetup();

// Upgrade the DB version
Mage::helper('sqlreport/InstallaManager')->checkInstall();

// End Sqlreport Setup
$installer->endSetup();
<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
  $installer->run("	 
	      CREATE TABLE catalog_product_history(
	      `producthistory_id` int(11) unsigned NOT NULL auto_increment,
	      `store_id` int(11) unsigned NOT NULL,	
	      `product_id` int(11) unsigned NOT NULL,
	      `date_update` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	      `price` decimal(12,4) NOT NULL,
	      PRIMARY KEY (`producthistory_id`)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8	 
	  ");


$setup->addAttribute('catalog_product', 'is_enabled_history', array(
    'group'         => 'Product history',
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Enable product hisotry ',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'default'   => '0'
    ));



$installer->endSetup();

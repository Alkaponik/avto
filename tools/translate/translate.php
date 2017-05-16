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
 * @category   Mage
 * @package    tools
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define( 'USAGE', <<<EOT

USAGE:

################################################################################

$> php -f translate.php -- --path ~/dev/magento/ --validate ru_RU [--file Mage_Adminhtml] [--file Mage_Catalog]

# Validates selected translation against the default (en_US)
# - checks for missing, redundant or duplicate keys

# missing - not present in default (english) csv, but present in validated file.
# redundant - not present in validated file, but present in default (english) csv
# duplicate - duplication in validated file

# Output example:

Mage_Adminhtml.csv:
    "My Wishlist" => missing
    "Report All Bugs" => missing
    "My Account" => redundant (137)
Mage_Catalog.csv:
    "Product Title" => redundant (245)
    "Attributes" => duplicate (119, 235, 307)

################################################################################

$> php -f translate.php -- --path ~/dev/magento/ --generate [--file Mage_Adminhtml] [--file Mage_Catalog]

# Generates the default translation (en_US)

# missing - present in locale but not present in module
# redundant - present in module but not present in locale
# duplicate - duplication in module

# Output example:

Created diffs:
    Mage_Adminhtml.1743-1802.csv
    Mage_Catalog.1747-1802.csv

Updated files:
    Mage_Adminhtml.csv
    Mage_Catalog.csv

################################################################################

$> php -f translate.php -- --path ~/dev/magento/ --update ru_RU [--file Mage_Adminhtml] [--file Mage_Catalog]

# Updates the selected translation with the changes (if any) from the default one (en_US)

# Output example:

Created diffs:
    Mage_Adminhtml.1743-1802.csv
    Mage_Catalog.1747-1802.csv

Updated files:
    Mage_Adminhtml.csv
    Mage_Catalog.csv

################################################################################

$> php -f translate.php -- --path ~/dev/magento/ --dups [--key "Checkout"]

# Checks for duplicate keys in different modules (in the default translation en_US)

# Output example:

"Checkout":
   Mage_Adminhtml.csv (1472) from app/code/core/Mage/Adminhtml/Block/Widget/Grid/Container.php (46)
   Mage_Catalog.csv (723) from design/frontend/default/default/catalog/product/view.phtml (172)

################################################################################

EOT
);

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(dirname(__FILE__)));

ini_set('include_path', ini_get('include_path')
    .PS.BP.'../../lib'
);

require_once 'config.inc.php';
require_once 'MultyGetopt.php';
require_once 'Varien/File/CsvMulty.php';
require_once 'Varien/Directory/Collection.php';
require_once 'CTranslate.php';

require_once 'Varien/Simplexml/Config.php';
require_once 'Varien/Simplexml/Element.php';

Translate::run($CONFIG);


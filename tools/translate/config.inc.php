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

define("EXTENSION",'csv');
$CONFIG['allow_extensions'] = array('php','xml','phtml','csv');
$CONFIG['paths'] = array(
    'locale' => 'app/locale/',
    'mage' => 'app/code/core/Mage/'
);

$CONFIG['translates'] = array(
    'Testimonial_MageDoc' => array(
        'app/code/local/Testimonial/MageDoc/',
        'app/design/adminhtml/default/default/template/magedoc/',
        'app/design/frontend/default/default/template/magedoc/'
    ),
);

$CONFIG['helpers']  = array(
    'magedoc'                         => 'Testimonial_MageDoc'
);

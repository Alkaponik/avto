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

    'Mage_Adminhtml' => array(
        'app/code/core/Mage/Admin/',
        'app/code/core/Mage/Adminhtml/',
        'app/design/adminhtml/default/default/layout/',
        'app/design/adminhtml/default/default/template/',
        '!app/design/adminhtml/default/default/layout/enterprise/', // ! = exclude
        '!app/design/adminhtml/default/default/template/enterprise/', // ! = exclude
    ),
    'Mage_AdminNotification' => array(
        'app/code/core/Mage/AdminNotification/',
    ),
    'Mage_Api' => array(
        'app/code/core/Mage/Api/',
        'app/design/adminhtml/default/default/template/',
        '!app/design/adminhtml/default/default/template/enterprise/', // ! = exclude
    ),
    'Mage_Backup' => array(
        'app/code/core/Mage/Backup/',
    ),
    'Mage_Bundle' => array(
        'app/code/core/Mage/Bundle/',
        'app/design/frontend/base/default/template/bundle/',
        'app/design/frontend/base/default/layout/bundle.xml',
        'app/design/frontend/default/modern/template/bundle/',
        'app/design/frontend/default/modern/layout/bundle.xml',
        'app/design/adminhtml/default/default/template/bundle/',
    ),
    'Mage_Catalog' => array(
        'app/code/core/Mage/Catalog/',
        'app/design/frontend/base/default/template/catalog/',
        'app/design/frontend/base/default/layout/catalog.xml',
        'app/design/frontend/default/modern/template/catalog/',
        'app/design/frontend/default/modern/layout/catalog.xml',
    ),
    'Mage_CatalogInventory' => array(
        'app/code/core/Mage/CatalogInventory/',
    ),
    'Mage_CatalogRule' => array(
        'app/code/core/Mage/CatalogRule/',
    ),
    'Mage_CatalogSearch' => array(
        'app/code/core/Mage/CatalogSearch/',
        'app/design/frontend/base/default/template/catalogsearch/',
        'app/design/frontend/base/default/layout/catalogsearch.xml',
        'app/design/frontend/default/modern/template/catalogsearch/',
        'app/design/frontend/default/modern/layout/catalogsearch.xml',
    ),
    'Mage_Checkout' => array(
        'app/code/core/Mage/Checkout/',
        'app/design/frontend/base/default/template/checkout/',
        'app/design/frontend/base/default/layout/checkout.xml',
        'app/design/frontend/default/modern/template/checkout/',
        'app/design/frontend/default/modern/layout/checkout.xml',
    ),
    'Mage_Chronopay' => array(
        'app/code/core/Mage/Chronopay/',
        'app/design/frontend/base/default/template/chronopay/',
        'app/design/frontend/base/default/layout/chronopay.xml',
    ),
    'Mage_Cms' => array(
        'app/code/core/Mage/Cms/',
        'app/design/frontend/base/default/template/cms/',
        'app/design/frontend/base/default/layout/cms.xml',
        'app/design/frontend/default/modern/template/cms/',
        'app/design/frontend/default/modern/layout/cms.xml',
    ),
    'Mage_Compiler' => array(
        'app/code/core/Mage/Compiler/',
        'app/design/adminhtml/default/default/template/compiler/',
        'app/design/adminhtml/default/default/layout/compiler.xml',
    ),
    'Mage_Contacts' => array(
        'app/code/core/Mage/Contacts/',
        'app/design/frontend/base/default/template/contacts/',
        'app/design/frontend/base/default/layout/contacts.xml',
        'app/design/frontend/default/modern/template/contacts/',
        'app/design/frontend/default/modern/layout/contacts.xml',
    ),
    'Mage_Core' => array(
        'app/code/core/Mage/Core/',
        'app/design/frontend/base/default/template/core/',
        'app/design/frontend/base/default/layout/core.xml',
        'app/design/frontend/default/modern/template/core/',
        'app/design/frontend/default/modern/layout/core.xml',
    ),
    'Mage_Cron' => array(
        'app/code/core/Mage/Cron/',
    ),
    'Mage_Customer' => array(
        'app/code/core/Mage/Customer/',
        'app/design/frontend/base/default/template/customer/',
        'app/design/frontend/base/default/layout/customer.xml',
        'app/design/frontend/default/modern/template/customer/',
        'app/design/frontend/default/modern/layout/customer.xml',
    ),
    'Mage_Cybermut' => array(
        'app/code/core/Mage/Cybermut/',
        'app/design/frontend/base/default/template/cybermut/',
        'app/design/frontend/base/default/layout/cybermut.xml',
    ),
    'Mage_Cybersource' => array(
        'app/code/core/Mage/Cybersource/',
        'app/design/frontend/base/default/template/cybersource/',
    ),
    'Mage_Dataflow' => array(
        'app/code/core/Mage/Dataflow/',
    ),
    'Mage_Directory' => array(
        'app/code/core/Mage/Directory/',
        'app/design/frontend/base/default/template/directory/',
        'app/design/frontend/base/default/layout/directory.xml',
        'app/design/frontend/default/modern/template/directory/',
        'app/design/frontend/default/modern/layout/directory.xml',
    ),
    'Mage_Downloadable' => array(
        'app/code/core/Mage/Downloadable/',
        'app/design/frontend/base/default/template/downloadable/',
        'app/design/frontend/base/default/layout/downloadable.xml',
        'app/design/frontend/default/modern/template/downloadable/',
        'app/design/frontend/default/modern/layout/downloadable.xml',
        'app/design/adminhtml/default/default/template/downloadable/',
    ),
    'Mage_Eav' => array(
        'app/code/core/Mage/Eav/',
    ),
    'Mage_Eway' => array(
        'app/code/core/Mage/Eway/',
        'app/design/frontend/base/default/template/eway/',
        'app/design/frontend/base/default/layout/eway.xml',
        'app/design/adminhtml/default/default/template/eway/',
    ),
    'Mage_Flo2Cash' => array(
        'app/code/core/Mage/Flo2Cash/',
        'app/design/frontend/base/default/template/flo2cash/',
        'app/design/adminhtml/default/default/template/flo2cash/',
    ),
    'Mage_GiftMessage' => array(
        'app/code/core/Mage/GiftMessage/',
        'app/design/frontend/base/default/template/giftmessage/',
        'app/design/frontend/default/modern/template/giftmessage/',
    ),
    'Mage_GoogleAnalytics' => array(
        'app/code/core/Mage/GoogleAnalytics/',
        'app/design/frontend/base/default/layout/googleanalytics.xml',
        'app/design/frontend/default/modern/layout/googleanalytics.xml',
    ),
    'Mage_GoogleBase' => array(
        'app/code/core/Mage/GoogleBase/',
        'app/design/adminhtml/default/default/template/googlebase/',
    ),
    'Mage_GoogleCheckout' => array(
        'app/code/core/Mage/GoogleCheckout/',
        'app/design/frontend/base/default/layout/googlecheckout.xml',
        'app/design/frontend/default/modern/layout/googlecheckout.xml',
    ),
    'Mage_GoogleOptimizer' => array(
        'app/code/core/Mage/GoogleOptimizer/',
        'app/design/frontend/base/default/layout/googleoptimizer.xml',
        'app/design/frontend/default/modern/layout/googleoptimizer.xml',
        'app/design/adminhtml/default/default/layout/googleoptimizer.xml',
        'app/design/adminhtml/default/default/template/googleoptimizer/',
    ),
    'Mage_Ideal' => array(
        'app/code/core/Mage/Ideal/',
        'app/design/frontend/base/default/template/ideal/',
        'app/design/frontend/base/default/layout/ideal.xml',
        'app/design/adminhtml/default/default/template/ideal/',
    ),
    'Mage_Index' => array(
        'app/code/core/Mage/Index',
        'app/design/adminhtml/default/default/layout/index.xml',
        'app/design/adminhtml/default/default/template/index/',
    ),
    'Mage_Install' => array(
        'app/code/core/Mage/Install/',
        'app/design/install/default/default/layout/',
        'app/design/install/default/default/template/',
    ),
    'Mage_Log' => array(
        'app/code/core/Mage/Log/',
    ),
    'Mage_Media' => array(
        'app/code/core/Mage/Media/',
    ),
    'Mage_Moneybookers' => array(
        'app/code/core/Mage/Moneybookers/',
        'app/design/frontend/base/default/template/moneybookers/',
        'app/design/frontend/base/default/layout/moneybookers.xml',
    ),
    'Mage_Newsletter' => array(
        'app/code/core/Mage/Newsletter/',
        'app/design/frontend/base/default/template/newsletter/',
        'app/design/frontend/base/default/layout/newsletter.xml',
        'app/design/frontend/default/modern/template/newsletter/',
        'app/design/frontend/default/modern/layout/newsletter.xml',
    ),
    'Mage_Ogone' => array(
        'app/code/core/Mage/Ogone/',
        'app/design/frontend/base/default/layout/ogone.xml',
        'app/design/frontend/base/default/template/ogone/',
    ),
    'Mage_Oscommerce' => array(
        'app/code/core/Mage/Oscommerce/',
        'app/design/frontend/base/default/template/oscommerce/',
        'app/design/frontend/base/default/layout/oscommerce.xml',
        'app/design/frontend/default/modern/template/oscommerce/',
        'app/design/frontend/default/modern/layout/oscommerce.xml',
        'app/design/adminhtml/default/default/template/oscommerce/',
    ),
    'Mage_Page' => array(
        'app/code/core/Mage/Page/',
        'app/design/frontend/base/default/template/page/',
        'app/design/frontend/base/default/layout/page.xml',
        'app/design/frontend/default/modern/template/page/',
        'app/design/frontend/default/modern/layout/page.xml',
    ),
    'Mage_Paybox' => array(
        'app/code/core/Mage/Paybox/',
        'app/design/frontend/base/default/template/paybox/',
        'app/design/frontend/base/default/layout/paybox.xml',
        'app/design/adminhtml/default/default/template/paybox/',
    ),
    'Mage_Paygate' => array(
        'app/code/core/Mage/Paygate/',
    ),
    'Mage_Payment' => array(
        'app/code/core/Mage/Payment/',
        'app/design/frontend/base/default/template/payment/',
        'app/design/frontend/default/modern/template/payment/',
    ),
    'Mage_Paypal' => array(
        'app/code/core/Mage/Paypal/',
        'app/design/frontend/base/default/template/paypal/',
        'app/design/frontend/base/default/layout/paypal.xml',
    ),
    'Mage_PaypalUk' => array(
        'app/code/core/Mage/Paypal/',
        'app/design/frontend/base/default/template/paypaluk/',
        'app/design/frontend/base/default/layout/paypaluk.xml',
    ),
    'Mage_Poll' => array(
        'app/code/core/Mage/Poll/',
        'app/design/frontend/base/default/template/poll/',
        'app/design/frontend/base/default/layout/poll.xml',
        'app/design/frontend/default/modern/template/poll/',
        'app/design/frontend/default/modern/layout/poll.xml',
    ),
    'Mage_ProductAlert' => array(
        'app/code/core/Mage/ProductAlert/',
        'app/design/frontend/base/default/template/email/productalert/',
        'app/design/frontend/base/default/template/productalert/',
        'app/design/frontend/base/default/layout/productalert.xml',
        'app/design/frontend/default/modern/template/email/productalert/',
        'app/design/frontend/default/modern/template/productalert/',
        'app/design/frontend/default/modern/layout/productalert.xml',
    ),
    'Mage_Protx' => array(
        'app/code/core/Mage/Protx/',
        'app/design/frontend/base/default/template/protx/',
        'app/design/frontend/base/default/layout/protx.xml',
    ),
    'Mage_Rating' => array(
        'app/code/core/Mage/Rating/',
        'app/design/frontend/base/default/template/rating/',
        'app/design/frontend/default/modern/template/rating/',
    ),
    'Mage_Reports' => array(
        'app/code/core/Mage/Reports/',
        'app/design/frontend/base/default/template/reports/',
        'app/design/frontend/base/default/layout/reports.xml',
        'app/design/frontend/default/modern/template/reports/',
        'app/design/frontend/default/modern/layout/reports.xml',
    ),
    'Mage_Review' => array(
        'app/code/core/Mage/Review/',
        'app/design/frontend/base/default/template/review/',
        'app/design/frontend/base/default/layout/review.xml',
        'app/design/frontend/default/modern/template/review/',
        'app/design/frontend/default/modern/layout/review.xml',
    ),
    'Mage_Rss' => array(
        'app/code/core/Mage/Rss/',
        'app/design/frontend/base/default/template/rss/',
        'app/design/frontend/base/default/layout/rss.xml',
        'app/design/frontend/default/modern/template/rss/',
        'app/design/frontend/default/modern/layout/rss.xml',
    ),
    'Mage_Rule' => array(
        'app/code/core/Mage/Rule/',
    ),
    'Mage_Sales' => array(
        'app/code/core/Mage/Sales/',
        'app/design/frontend/base/default/template/email/order/',
        'app/design/frontend/base/default/template/sales/',
        'app/design/frontend/base/default/layout/sales.xml',
        'app/design/frontend/default/modern/template/email/order/',
        'app/design/frontend/default/modern/template/sales/',
        'app/design/frontend/default/modern/layout/sales.xml',
    ),
    'Mage_SalesRule' => array(
        'app/code/core/Mage/SalesRule/',
    ),
    'Mage_Sendfriend' => array(
        'app/code/core/Mage/Sendfriend/',
        'app/design/frontend/base/default/template/sendfriend/',
        'app/design/frontend/base/default/layout/sendfriend.xml',
        'app/design/frontend/default/modern/template/sendfriend/',
        'app/design/frontend/default/modern/layout/sendfriend.xml',
    ),
    'Mage_Shipping' => array(
        'app/code/core/Mage/Shipping/',
        'app/design/frontend/base/default/template/shipping/',
        'app/design/frontend/base/default/layout/shipping.xml',
        'app/design/frontend/default/modern/template/shipping/',
        'app/design/frontend/default/modern/layout/shipping.xml',
    ),
    'Mage_Sitemap' => array(
        'app/code/core/Mage/Sitemap/',
    ),
    'Mage_Strikeiron' => array(
        'app/code/core/Mage/Strikeiron/',
    ),
    'Mage_Tag' => array(
        'app/code/core/Mage/Tag/',
        'app/design/frontend/base/default/template/tag/',
        'app/design/frontend/base/default/layout/tag.xml',
        'app/design/frontend/default/modern/template/tag/',
        'app/design/frontend/default/modern/layout/tag.xml',
    ),
    'Mage_Tax' => array(
        'app/code/core/Mage/Tax/',
    ),
    'Mage_Usa' => array(
        'app/code/core/Mage/Usa/',
    ),
    'Mage_Weee' => array(
        'app/code/core/Mage/Weee/',
        #'app/design/frontend/base/default/template/weee/',
        'app/design/frontend/base/default/layout/weee.xml',
        #'app/design/frontend/default/modern/template/weee/',
        #'app/design/frontend/default/modern/layout/weee.xml',
    ),
    'Mage_Wishlist' => array(
        'app/code/core/Mage/Wishlist/',
        'app/design/frontend/base/default/template/wishlist/',
        'app/design/frontend/base/default/layout/wishlist.xml',
        'app/design/frontend/default/modern/template/wishlist/',
        'app/design/frontend/default/modern/layout/wishlist.xml',
    ),
    'Mage_Widget' => array(
        'app/code/core/Mage/Widget/',
        'app/design/adminhtml/default/default/layout/widget.xml',
        'app/design/adminhtml/default/default/template/widget/',
    ),
    'translate' => array(
        'app/design/frontend/base/default/template/callouts/',
        'app/design/frontend/default/modern/template/callouts/',
    ),
    'Enterprise_AdminGws' => array(
        'app/code/core/Enterprise/AdminGws/',
        'app/design/adminhtml/default/default/layout/enterprise/admingws.xml',
        'app/design/adminhtml/default/default/template/enterprise/admingws/',
    ),
    'Enterprise_Banner' => array(
        'app/code/core/Enterprise/Banner/',
        'app/design/adminhtml/default/default/layout/enterprise/banner.xml',
        'app/design/adminhtml/default/default/template/enterprise/banner/',
        'app/design/frontend/enterprise/default/template/banner/'
    ),
    'Enterprise_CatalogEvent' => array(
        'app/code/core/Enterprise/CatalogEvent/',
        'app/design/adminhtml/default/default/layout/enterprise/catalogevent.xml',
        'app/design/adminhtml/default/default/template/enterprise/catalogevent/',
        'app/design/frontend/enterprise/default/layout/catalogevent.xml',
        'app/design/frontend/enterprise/default/template/catalogevent/'
    ),
    'Enterprise_CatalogPermissions' => array(
        'app/code/core/Enterprise/CatalogPermissions/',
        'app/design/adminhtml/default/default/layout/enterprise/catalogpermissions.xml',
        'app/design/adminhtml/default/default/template/enterprise/catalogpermissions',
        'app/design/frontend/enterprise/default/layout/catalogpermissions.xml'
    ),
    'Enterprise_Cms' => array(
        'app/code/core/Enterprise/Cms/',
        'app/design/adminhtml/default/default/layout/enterprise/cms.xml',
        'app/design/adminhtml/default/default/template/enterprise/cms/',
        'app/design/frontend/enterprise/default/layout/cms.xml',
        'app/design/frontend/enterprise/default/template/cms/'
    ),
    'Enterprise_CustomerBalance' => array(
        'app/code/core/Enterprise/CustomerBalance/',
        'app/design/adminhtml/default/default/layout/enterprise/customerbalance.xml',
        'app/design/adminhtml/default/default/template/enterprise/customerbalance/',
        'app/design/frontend/enterprise/default/layout/customerbalance.xml',
        'app/design/frontend/enterprise/default/template/customerbalance/'
    ),
    'Enterprise_CustomerSegment' => array(
        'app/code/core/Enterprise/CustomerSegment/',
        'app/design/adminhtml/default/default/layout/enterprise/customersegment.xml',
    ),
    'Enterprise_Enterprise' => array(
        'app/code/core/Enterprise/Enterprise/',
    ),
    'Enterprise_GiftCard' => array(
        'app/code/core/Enterprise/GiftCard/',
        'app/design/adminhtml/default/default/layout/enterprise/giftcard.xml',
        'app/design/adminhtml/default/default/template/enterprise/giftcard/',
        'app/design/frontend/enterprise/default/layout/giftcard.xml',
        'app/design/frontend/enterprise/default/template/giftcard/'
    ),
    'Enterprise_GiftCardAccount' => array(
        'app/code/core/Enterprise/GiftCardAccount/',
        'app/design/adminhtml/default/default/layout/enterprise/giftcardaccount.xml',
        'app/design/adminhtml/default/default/template/enterprise/giftcardaccount/',
        'app/design/frontend/enterprise/default/layout/giftcardaccount.xml',
        'app/design/frontend/enterprise/default/template/giftcardaccount/'
    ),
    'Enterprise_Invitation' => array(
        'app/code/core/Enterprise/Invitation/',
        'app/design/adminhtml/default/default/layout/enterprise/invitation.xml',
        'app/design/adminhtml/default/default/template/enterprise/invitation/',
        'app/design/frontend/enterprise/default/layout/invitation.xml',
        'app/design/frontend/enterprise/default/template/invitation/'
    ),
    'Enterprise_Logging' => array(
        'app/code/core/Enterprise/Logging/',
        'app/design/adminhtml/default/default/layout/enterprise/logging.xml',
        'app/design/adminhtml/default/default/template/enterprise/logging/',
    ),
    'Enterprise_Pci' => array(
        'app/code/core/Enterprise/Pci/',
        'app/design/adminhtml/default/default/layout/enterprise/pci.xml',
    ),
    'Enterprise_Reward' => array(
        'app/code/core/Enterprise/Reward/',
        'app/design/adminhtml/default/default/layout/enterprise/reward.xml',
        'app/design/adminhtml/default/default/template/enterprise/reward/',
        'app/design/frontend/enterprise/default/layout/reward.xml',
        'app/design/frontend/enterprise/default/template/reward/'
    ),
    'Enterprise_Staging' => array(
        'app/code/core/Enterprise/Staging/',
        'app/design/adminhtml/default/default/layout/enterprise/staging.xml',
        'app/design/adminhtml/default/default/template/enterprise/staging/',
    ),
    'Enterprise_TargetRule' => array(
        'app/code/core/Enterprise/TargetRule/',
        'app/design/adminhtml/default/default/layout/enterprise/targetrule.xml',
        'app/design/adminhtml/default/default/template/enterprise/targetrule/',
        'app/design/frontend/enterprise/default/layout/targetrule.xml',
        'app/design/frontend/enterprise/default/template/targetrule/'
    ),
    'Enterprise_WebsiteRestriction' => array(
        'app/code/core/Enterprise/WebsiteRestriction/',
        'app/design/frontend/enterprise/default/layout/websiterestriction.xml'
    ),
);

$CONFIG['helpers']  = array(
    'adminhtml'         => 'Mage_Adminhtml',
    'adminnotification' => 'Mage_AdminNotification',
    'api'               => 'Mage_Api',
    'backup'            => 'Mage_Backup',
    'bundle'            => 'Mage_Bundle',
    'catalog'           => 'Mage_Catalog',
    'cataloginventory'  => 'Mage_CatalogInventory',
    'catalogrule'       => 'Mage_CatalogRule',
    'catalogsearch'     => 'Mage_CatalogSearch',
    'checkout'          => 'Mage_Checkout',
    'chronopay'         => 'Mage_Chronopay',
    'cms'               => 'Mage_Cms',
    'compiler'          => 'Mage_Compiler',
    'contacts'          => 'Mage_Contacts',
    'core'              => 'Mage_Core',
    'cron'              => 'Mage_Cron',
    'customer'          => 'Mage_Customer',
    'cybermut'          => 'Mage_Cybermut',
    'cybersource'       => 'Mage_Cybersource',
    'dataflow'          => 'Mage_Dataflow',
    'directory'         => 'Mage_Directory',
    'downloadable'      => 'Mage_Downloadable',
    'eav'               => 'Mage_Eav',
    'eway'              => 'Mage_Eway',
    'flo2cash'          => 'Mage_Flo2Cash',
    'giftmessage'       => 'Mage_GiftMessage',
    'googleanalytics'   => 'Mage_GoogleAnalytics',
    'googlebase'        => 'Mage_GoogleBase',
    'googlecheckout'    => 'Mage_GoogleCheckout',
    'googleoptimizer'   => 'Mage_GoogleOptimizer',
    'ideal'             => 'Mage_Ideal',
    'index'             => 'Mage_Index',
    'install'           => 'Mage_Install',
    'log'               => 'Mage_Log',
    'media'             => 'Mage_Media',
    'moneybookers'      => 'Mage_Moneybookers',
    'newsletter'        => 'Mage_Newsletter',
    'ogone'             => 'Mage_Ogone',
    'oscommerce'        => 'Mage_Oscommerce',
    'page'              => 'Mage_Page',
    'paybox'            => 'Mage_Paybox',
    'paygate'           => 'Mage_Paygate',
    'payment'           => 'Mage_Payment',
    'paypal'            => 'Mage_Paypal',
    'paypaluk'          => 'Mage_PaypalUk',
    'poll'              => 'Mage_Poll',
    'productalert'      => 'Mage_ProductAlert',
    'protx'             => 'Mage_Protx',
    'rating'            => 'Mage_Rating',
    'reports'           => 'Mage_Reports',
    'review'            => 'Mage_Review',
    'rss'               => 'Mage_Rss',
    'rule'              => 'Mage_Rule',
    'sales'             => 'Mage_Sales',
    'salesrule'         => 'Mage_SalesRule',
    'sendfriend'        => 'Mage_Sendfriend',
    'shipping'          => 'Mage_Shipping',
    'sitemap'           => 'Mage_Sitemap',
    'strikeiron'        => 'Mage_Strikeiron',
    'tag'               => 'Mage_Tag',
    'tax'               => 'Mage_Tax',
    'usa'               => 'Mage_Usa',
    'weee'              => 'Mage_Weee',
    'wishlist'          => 'Mage_Wishlist',
    'widget'            => 'Mage_Widget',
    'enterprise_admingws'           => 'Enterprise_AdminGws',
    'enterprise_banner'             => 'Enterprise_Banner',
    'enterprise_catalogevent'       => 'Enterprise_CatalogEvent',
    'enterprise_catalogpermissions' => 'Enterprise_CatalogPermissions',
    'enterprise_cms'                => 'Enterprise_Cms',
    'enterprise_customerbalance'    => 'Enterprise_CustomerBalance',
    'enterprise_customersegment'    => 'Enterprise_CustomerSegment',
    'enterprise_enterprise'         => 'Enterprise_Enterprise',
    'enterprise_giftcard'           => 'Enterprise_GiftCard',
    'enterprise_giftcardaccount'    => 'Enterprise_GiftCardAccount',
    'enterprise_invitation'         => 'Enterprise_Invitation',
    'enterprise_logging'            => 'Enterprise_Logging',
    'enterprise_pci'                => 'Enterprise_Pci',
    'enterprise_reward'             => 'Enterprise_Reward',
    'enterprise_staging'            => 'Enterprise_Staging',
    'enterprise_targetrule'         => 'Enterprise_TargetRule',
    'enterprise_websiterestriction' => 'Enterprise_WebsiteRestriction',
);

<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Analytics_Model_Observer
{
    /**
     * Handler for system config save after event
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    public function actionConfigSaveAfter($observer)
    {
        $savedTimezone = Mage::getStoreConfig('general/locale/timezone');
        $lastTimezone = Mage::getStoreConfig(Oro_Analytics_Helper_Data::XML_CURRENT_STORE_TIMEZONE);
        if ($savedTimezone !== $lastTimezone) {
            Mage::getResourceModel('oro_analytics/dailyAggregation')->truncateTables();
            Mage::getModel('core/config')->saveConfig(Oro_Analytics_Helper_Data::XML_CURRENT_STORE_TIMEZONE,
                $savedTimezone);
        }
    }
}

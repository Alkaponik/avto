<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Analytics
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Analytics_Model_Refer extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('oro_analytics/refer');
    }
}

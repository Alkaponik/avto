<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Model_Resource_Role extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('oro_dashboard/permissions_role', 'id');
    }

    /**
     * Remove roles for dashboard
     *
     * @param  int  $dashboardId
     * @return null
     */
    public function remove($dashboardId)
    {
        $condition = array(
            'dashboard_id = ?' => (int) $dashboardId,
        );

        $this->_getWriteAdapter()->delete($this->getMainTable(), $condition);
    }
}

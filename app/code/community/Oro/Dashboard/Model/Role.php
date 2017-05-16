<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Model_Role extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('oro_dashboard/role');
    }

    /**
     * Get dashboard role ids filtered by field/value
     * @param  int    $dashboardId
     * @param  string $field
     * @param  string $value
     * @return array
     */
    public function getRoleIds($dashboardId, $field, $value)
    {

        $collection = $this->getCollection()->addFieldToFilter("dashboard_id", $dashboardId)
            ->addFieldToFilter($field, $value);
        $roleIds = array();

        foreach ($collection as $item) {
            $roleIds[] = $item->getUserRoleId();
        }

        return $roleIds;
    }
}

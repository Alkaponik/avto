<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Model_User extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('oro_dashboard/user');
    }

    /**
     * Get dashboard user ids filtered by field/value
     * @param  int    $dashboardId
     * @param  string $field
     * @param  string $value
     * @return array
     */
    public function getUserIds($dashboardId, $field, $value)
    {
        $collection = $this->getCollection()->addFieldToFilter("dashboard_id", $dashboardId)
            ->addFieldToFilter($field, $value);

        $userIds = array();

        foreach ($collection as $item) {
            $userIds[] = $item->getUserId();
        }

        return $userIds;
    }

    /**
     * Get User Default Dashboard Id
     * @param  int $userId
     * @return int
     */
    public function getDefaultDashboardId($userId)
    {
        return $this->getResource()->getDefaultDashboardId($userId);
    }

}

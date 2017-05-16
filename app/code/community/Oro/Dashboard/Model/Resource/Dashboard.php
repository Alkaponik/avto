<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Model_Resource_Dashboard extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('oro_dashboard/dashboard', 'id');
    }

    /**
     * Check if user can edit dashboard
     *
     * @param  int     $dashboardId
     * @param  int     $userId
     * @return boolean
     */
    public function canEdit($dashboardId, $userId)
    {
        $roleId = Mage::getModel("admin/user")->load($userId)->getRole()->getRoleId();

        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(array("main_table" => $this->getMainTable()), "main_table.id")
            ->joinLeft(
            array("dashboard_users" => $this->getTable("oro_dashboard/permissions_user")),
            "main_table.id=dashboard_users.dashboard_id AND dashboard_users.user_id = :user_id",
            array()
        )->joinLeft(
            array("dashboard_roles" => $this->getTable("oro_dashboard/permissions_role")),
            "main_table.id=dashboard_roles.dashboard_id AND dashboard_roles.user_role_id = :role_id",
            array()
        )->where('main_table.id = :dashboard_id')
            ->where('dashboard_users.edit > 0 OR dashboard_roles.edit > 0');

        $binds = array(
            ":dashboard_id" => $dashboardId,
            ":user_id" => $userId,
            ":role_id" => $roleId
        );

        return $adapter->fetchRow($select, $binds);
    }

    /**
     * Check if user can view dashboard
     *
     * @param  int     $dashboardId
     * @param  int     $userId
     * @return boolean
     */
    public function canView($dashboardId, $userId)
    {
        $roleId = Mage::getModel("admin/user")->load($userId)->getRole()->getRoleId();

        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(array("main_table" => $this->getMainTable()), "main_table.id")
            ->joinLeft(
            array("dashboard_users" => $this->getTable("oro_dashboard/permissions_user")),
            "main_table.id=dashboard_users.dashboard_id AND dashboard_users.user_id = :user_id",
            array()
        )->joinLeft(
            array("dashboard_roles" => $this->getTable("oro_dashboard/permissions_role")),
            "main_table.id=dashboard_roles.dashboard_id AND dashboard_roles.user_role_id = :role_id",
            array()
        )->where('main_table.id = :dashboard_id')
            ->where('dashboard_users.view > 0 OR dashboard_roles.view > 0');

        $binds = array(
            ":dashboard_id" => $dashboardId,
            ":user_id" => $userId,
            ":role_id" => $roleId
        );

        return $adapter->fetchRow($select, $binds);
    }
}

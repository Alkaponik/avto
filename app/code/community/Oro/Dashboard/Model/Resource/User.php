<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Model_Resource_User extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('oro_dashboard/permissions_user', 'id');
    }

    /**
     * Remove users for dashboard
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

    /**
     * Set default dashboard for user
     *
     * @param  int  $dashboardId
     * @param  int  $userId
     * @return null
     */
    public function setDefault($dashboardId, $userId)
    {
        /* Remove old default dashboard for user */
        $condition = array(
            'user_id = ?' => (int) $userId,
        );

        $bind = array(
            'is_default' => 0
        );

        $this->_getWriteAdapter()->update($this->getMainTable(), $bind, $condition);

        $bind = array(
            'dashboard_id' => (int) $dashboardId,
            'user_id' => (int) $userId,
            'is_default' => 1
        );

        $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $bind, array("is_default"));
    }

    /**
     * Set dashboard non-default for user
     *
     * @param  int  $dashboardId
     * @param  int  $userId
     * @return null
     */
    public function setNotDefault($dashboardId, $userId)
    {
        /* Remove old default dashboard for user */
        $condition = array(
            'user_id = ?' => (int) $userId,
            'dashboard_id = ?' => (int) $dashboardId,
        );

        $bind = array(
            'is_default' => 0
        );

        $this->_getWriteAdapter()->update($this->getMainTable(), $bind, $condition);
    }

    /**
     * Get default dashboard id for user
     *
     * @param  int $userId
     * @return int
     */
    public function getDefaultDashboardId($userId)
    {
        $roleId = Mage::getModel("admin/user")->load($userId)->getRole()->getRoleId();

        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(array("main_table" => $this->getTable("oro_dashboard/dashboard")), "main_table.id")
            ->joinLeft(
            array("dashboard_users" => $this->getTable("oro_dashboard/permissions_user")),
            "main_table.id=dashboard_users.dashboard_id AND dashboard_users.user_id = :user_id",
            array("dashboard_users.is_default as user_default")
        )->joinLeft(
            array("dashboard_roles" => $this->getTable("oro_dashboard/permissions_role")),
            "main_table.id=dashboard_roles.dashboard_id AND dashboard_roles.user_role_id = :role_id",
            array("dashboard_roles.is_default as role_default")
        )->where('dashboard_users.is_default > 0 OR dashboard_roles.is_default > 0')
            ->order("dashboard_users.is_default DESC")
            ->limit(1);

        $binds = array(
            ":user_id" => $userId,
            ":role_id" => $roleId
        );

        $data = $adapter->fetchRow($select, $binds);
        if ($data) {
            return $data['id'];
        } else {
            return 0;
        }
    }
}

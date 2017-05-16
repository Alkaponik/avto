<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Model_Resource_Dashboard_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        $this->_init('oro_dashboard/dashboard');
    }

    /**
     * Join roles and users to collection
     * @param  Mage_Admin_Model_User                             $user
     * @return Oro_Dashboard_Model_Resource_Dashboard_Collection
     */
    public function joinRolesUsers($user = false)
    {

        $this->getSelect()->joinLeft(
            array("dashboard_users" => $this->getTable("oro_dashboard/permissions_user")),
            'main_table.id = dashboard_users.dashboard_id',
            array()
        )->joinLeft(
            array("users" => $this->getTable("admin/user")),
            'dashboard_users.user_id=users.user_id',
            array("GROUP_CONCAT(DISTINCT username SEPARATOR ',') users")
        )->joinLeft(
            array("dashboard_roles" => $this->getTable("oro_dashboard/permissions_role")),
            'main_table.id = dashboard_roles.dashboard_id',
            array()
        )->joinLeft(
            array("roles" => $this->getTable("admin/role")),
            'dashboard_roles.user_role_id = roles.role_id',
            array("GROUP_CONCAT(DISTINCT roles.role_name SEPARATOR ',') roles")
        )->joinLeft(
            array("defaultroles" => $this->getTable("admin/role")),
            'dashboard_roles.user_role_id = defaultroles.role_id and dashboard_roles.is_default = 1',
            array("GROUP_CONCAT(DISTINCT defaultroles.role_name SEPARATOR ',') is_default_for"))
            ->group("main_table.id");

        /*If we need to check users permissions*/
        if ($user) {
            $userId = $user->getId();
            $roleId = $user->getRole()->getRoleId();

            $this->getSelect()->where("dashboard_users.user_id=?", $userId)
                ->orWhere("dashboard_roles.user_role_id=?", $roleId)
                ->orWhere("main_table.created_by=?", $userId);
        }

        return $this;
    }

    /**
     * Add user filter to collection
     * @param  Mage_Admin_Model_User                             $user
     * @return Oro_Dashboard_Model_Resource_Dashboard_Collection
     */
    public function addUserToFilter($user)
    {

        $userId = $user->getId();
        $roleId = $user->getRole()->getRoleId();

        $this->getSelect()->joinLeft(
            array("dashboard_users" => $this->getTable("oro_dashboard/permissions_user")),
            "main_table.id = dashboard_users.dashboard_id AND dashboard_users.user_id = $userId",
            array()
        )->joinLeft(
            array("dashboard_roles" => $this->getTable("oro_dashboard/permissions_role")),
            "main_table.id = dashboard_roles.dashboard_id AND dashboard_roles.user_role_id = $roleId",
            array()
        )->columns('(IFNULL(dashboard_users.view,0) + IFNULL(dashboard_roles.view,0)) view_cnt, (IFNULL(dashboard_users.edit ,0) + IFNULL(dashboard_roles.edit,0)) edit_cnt')
            ->having("view_cnt>0 OR edit_cnt >0");

        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        $countSelect->reset(Zend_Db_Select::GROUP);

        return $countSelect;
    }
}

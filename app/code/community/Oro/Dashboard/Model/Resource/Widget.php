<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Model_Resource_Widget extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Widget Relation table name
     *
     * @var string
     */
    protected $_widgetRelationTable;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('oro_dashboard/widget', 'id');
        $this->_widgetRelationTable = $this->getTable('oro_dashboard/widget_relation');
    }

    /**
     * Assign widget to dashboard, by default 1st column at the top
     * @param int $widgetId
     * @param int $dashboardId
     */
    public function assignToDashboard($widgetId, $dashboardId)
    {
        $this->changePosition($widgetId, $dashboardId, 1, $this->getColumnMinOrder($dashboardId, 1) - 1);
    }

    /**
     * Change widget position
     * @param int $widgetId
     * @param int $dashboardId
     * @param int $columnId
     * @param int $position
     *
     */
    public function changePosition($widgetId, $dashboardId, $columnId, $position)
    {
        $bind = array(
            'dashboard_id' => (int) $dashboardId,
            'widget_id' => (int) $widgetId,
            'position_column' => (int) $columnId,
            'position_order' => (int) $position
        );

        $this->_getWriteAdapter()->insertOnDuplicate($this->_widgetRelationTable, $bind, array("position_column", "position_order"));
    }

    /**
     * Get min widget position order in column for dashboard
     *
     * @param  int $dashboardId
     * @param  int $columnId
     * @return int
     */
    public function getColumnMinOrder($dashboardId, $columnId)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(array("main_table" => $this->_widgetRelationTable), "MIN(main_table.position_order) min_order")
            ->where('main_table.dashboard_id = :dasbhoard_id')
            ->where('main_table.position_column = :position_column');

        $binds = array(
            ":dasbhoard_id" => $dashboardId,
            ":position_column" => $columnId
        );

        $data = $adapter->fetchRow($select, $binds);
        if ($data['min_order']) {
            return $data['min_order'];
        } else {
            return 100; //Max widgets count in column
        }
    }

    /**
     * Remove widgets associated with dashboard
     * @param  int                                 $dashboardId
     * @return Oro_Dashboard_Model_Resource_Widget
     */
    public function removeDashboardWidgets($dashboardId)
    {
        $dashboardId = (int) $dashboardId;
        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select()
            ->from(array("main_table" => $this->_widgetRelationTable), "widget_id")
            ->where('main_table.dashboard_id = :dasbhoard_id');

        $binds = array(
            ":dasbhoard_id" => $dashboardId,
        );

        $delete = $adapter->fetchAll($select, $binds);

        foreach ($delete as $widgetId) {
            $condition = array('id = ?' => $widgetId["widget_id"]);
            $adapter->delete($this->getMainTable(), $condition);
        }

        return $this;
    }
}

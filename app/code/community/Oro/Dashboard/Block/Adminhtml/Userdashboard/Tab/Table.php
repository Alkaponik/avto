<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Tab_Table extends Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Table
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('oro_dashboard/tab/table.phtml');

        return $this;
    }
}

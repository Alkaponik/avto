<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Tab_Timeline extends Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Timeline
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('oro_dashboard/tab/timeline.phtml');

        return $this;
    }
}

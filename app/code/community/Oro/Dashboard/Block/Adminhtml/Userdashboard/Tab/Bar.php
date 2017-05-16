<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Tab_Bar extends Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Bar
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('oro_dashboard/tab/bar.phtml');

        return $this;
    }
}

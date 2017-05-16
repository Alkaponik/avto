<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Tab_Pie extends Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Pie
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('oro_dashboard/tab/pie.phtml');

        return $this;
    }
}

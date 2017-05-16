<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Tab_Metric extends Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Metric
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('oro_dashboard/tab/metric.phtml');

        return $this;
    }
}

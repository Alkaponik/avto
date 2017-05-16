<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Pie extends Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Abstract
{
    const SECTORS_LIMIT = 10;

    protected function _prepareLayout()
    {
        $this->setTemplate('oro_dashboard/widget/pie.phtml');

        return parent::_prepareLayout();
    }

    /**
     * Get widget group by attribute
     *
     * @return string
     */
    public function getGroupedByAttribute()
    {
        return $this->getWidgetConfig()->getGroupedByAttribute();
    }

    /**
     * Get pie sectors limit
     *
     * @return int
     */
    public function getSectorsNumber()
    {
        return $this->getWidgetConfig()->getSectorsNumber();
    }

    /**
     * Get widget data
     *
     * @return string
     */
    public function getWidgetData()
    {
        /** @var $helper Oro_Dashboard_Helper_Data */
        $helper = Mage::helper('oro_dashboard');
        $widgetData = '';
        $metricClass = $helper->getMetricsClass($this->getMetric());
        if ($metricClass) {
            $metric = new $metricClass;
            $metric->setWidget($this);
            $data = $metric->getDataForPie($helper->getAttributeData($this->getGroupedByAttribute()), ($this->getSectorsNumber() - 1));
            $allData = $metric->getData();
            $pieData = array();
            $sum = 0;
            if ($data) {
                foreach ($data as $grouped) {
                    $pieData[] = "['" . addslashes($grouped['group_value']) . "'," . (int)$grouped['count'] . "]";
                    $sum += $grouped['count'];
                }
                $pieData[] = "['" . addslashes($this->__("Other")) . "'," . ($allData - $sum) . "]";
                $pieData = implode(",", $pieData);
            }
        }
        if ($pieData) {
            $widgetData = "[" . $pieData . "]";
        } else {
            $widgetData = '';
        }

        return $widgetData;
    }
}

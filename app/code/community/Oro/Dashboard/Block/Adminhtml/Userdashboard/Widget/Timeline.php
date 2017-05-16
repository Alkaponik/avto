<?php
/**
* {magecore_license_notice}
*
* @category   MageCore
* @package    Dashboard
* @copyright  {magecore_copyright}
* @license    {magecore_license}
*/

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Timeline 
    extends Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Abstract
{
    protected function _prepareLayout()
    {
        $this->setTemplate('oro_dashboard/widget/timeline.phtml');
        return parent::_prepareLayout();
    }

    /**
     * Get widget metric compare
     *
     * @return string
     */
    public function getMetricCompare()
    {
        return $this->getWidgetConfig()->getMetricCompare();
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
        $lineMain = array();
        $lineCompare = array();

        $dataFull = array();
        $dataFullCompare = array();

        $isHourlyPlot = $helper->isHourlyPlot();
        if ($metricClass) {
            $metric = new $metricClass;
            $metric->setWidget($this);
            if ($data = $metric->getDataForTimeline()) {
                if ($isHourlyPlot) {
                    foreach ($data as $point) {
                        if ($point['count'] != 0) {
                            $this->_valuesArray['main'][] = (int)$point['count'];
                        }
                        $lineMain[] = "['" . $helper->getStoreDate($point['period_grouped']) . "'," . (int)$point['count'] . "]";
                        $dataFull[$helper->getStoreDate($point['period_grouped'])] = "['" . $helper->getStoreDate($point['period_grouped']) . "'," . (int)$point['count'] . "]";
                    }
                } else {
                    foreach ($data as $point) {
                        if ($point['count'] != 0) {
                            $this->_valuesArray['main'][] = (int)$point['count'];
                        }
                        $lineMain[] = "['" . $point['period_grouped'] . "'," . (int)$point['count'] . "]";
                        $dataFull[$point['period_grouped']] = "['" . $point['period_grouped'] . "'," . (int)$point['count'] . "]";
                    }
                }

                $lineMain = implode(",", $lineMain);
                $dataFull = implode(",", $dataFull);
            }
        }
        if ($this->getMetricCompare()) {
            $metricClass = $helper->getMetricsClass($this->getMetricCompare());
            if ($metricClass) {
                $metric = new $metricClass;
                $metric->setWidget($this);
                $metric->setIsCompare(true);
                if ($data = $metric->getDataForTimeline()) {
                    if ($isHourlyPlot) {
                        foreach ($data as $point) {
                            if ($point['count'] != 0) {
                                $this->_valuesArray['compare'][] = (int)$point['count'];
                            }
                            $lineCompare[] = "['" . $helper->getStoreDate($point['period_grouped']) . "'," . (int)$point['count'] . "]";
                            $dataFullCompare[$helper->getStoreDate($point['period_grouped'])] = "['" . $helper->getStoreDate($point['period_grouped']) . "'," . (int)$point['count'] . "]";
                        }
                    } else {
                        foreach ($data as $point) {
                            if ($point['count'] != 0) {
                                $this->_valuesArray['compare'][] = (int)$point['count'];
                            }
                            $lineCompare[] = "['" . $point['period_grouped'] . "'," . (int)$point['count'] . "]";
                            $dataFullCompare[$point['period_grouped']] = "['" . $point['period_grouped'] . "'," . (int)$point['count'] . "]";
                        }
                    }
                    $lineCompare = implode(",", $lineCompare);
                    $dataFullCompare = implode(",", $dataFullCompare);
                }
            }
        }
        if ($lineMain) {
            // $widgetData = "[" . $lineMain . ($lineCompare ? "],[" . $lineCompare . "]" : "]");
            $widgetData = "[" . $dataFull . ($dataFullCompare ? "],[" . $dataFullCompare . "]" : "]");
        } else {
            $widgetData = '';
        }

        return $widgetData;
    }
}

<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Bar extends Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Abstract
{
    /**
     * @var array
     */
    protected $_ticks = array();

    protected function _prepareLayout()
    {
        $this->setTemplate('oro_dashboard/widget/bar.phtml');

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
     * Get ticks for monthly plot
     *
     * @return string
     */
    public function getTicks()
    {
        return implode(",", $this->_ticks);
    }

    /**
     * Check if plot is monthly
     *
     * @return string
     */
    public function isMonthlyPlot()
    {
        /** @var $helper Oro_Dashboard_Helper_Data */
        $helper = Mage::helper('oro_dashboard');
        if ($helper->getBarPlotType() === "monthly") {
            return true;
        }

        return false;
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
        $isHourlyPlot = $helper->isHourlyPlot();
        if ($metricClass) {
            $metric = new $metricClass;
            $metric->setPlotType($helper->getBarPlotType());
            $metric->setWidget($this);
            if ($data = $metric->getDataForTimeline()) {
                if ($this->isMonthlyPlot()) {
                    foreach ($data as $point) {
                        $lineMain[$point['period_grouped']] = (int)$point['count'];
                        $this->_ticks[$point['period_grouped']] = "'" . $helper->getLocaleMonth($point['period_grouped']) . "'";
                        $this->_valuesArray['main'][] = (int)$point['count'];
                    }
                } else {
                    $lineMain[] = "['" . $helper->getDateFrom(false, true, true) . "',0]";
                    if ($isHourlyPlot) {
                        foreach ($data as $point) {
                            $lineMain[] = "['" . $helper->getStoreDate($point['period_grouped']) . "'," . (int)$point['count'] . "]";
                            $this->_valuesArray['main'][] = (int)$point['count'];
                        }
                    } else {
                        foreach ($data as $point) {
                            $lineMain[] = "['" . $point['period_grouped'] . "'," . (int)$point['count'] . "]";
                            $this->_valuesArray['main'][] = (int)$point['count'];
                        }
                    }
                    $lineMain[] = "['" . $helper->getDateTo(false, true, true) . "',0]";
                }
            }
        }
        if ($this->getMetricCompare()) {
            $metricClass = $helper->getMetricsClass($this->getMetricCompare());
            if ($metricClass) {
                $metric = new $metricClass;
                $metric->setPlotType($helper->getBarPlotType());
                $metric->setWidget($this);
                $metric->setIsCompare(true);
                if ($data = $metric->getDataForTimeline()) {
                    if ($this->isMonthlyPlot()) {
                        foreach ($data as $point) {
                            $lineCompare[$point['period_grouped']] = (int)$point['count'];
                            $this->_valuesArray['compare'][] = (int)$point['count'];
                            if (!isset($this->_ticks[$point['period_grouped']])) {
                                $this->_ticks[$point['period_grouped']] = "'" . $helper->getLocaleMonth($point['period_grouped']) . "'";
                            }
                        }
                    } else {
                        $lineCompare[] = "['" . $helper->getDateFrom(false, true, true) . "',0]";
                        if ($isHourlyPlot) {
                            foreach ($data as $point) {
                                $this->_valuesArray['compare'][] = (int)$point['count'];
                                $lineCompare[] = "['" . $helper->getStoreDate($point['period_grouped']) . "'," . (int)$point['count'] . "]";
                            }
                        } else {
                            foreach ($data as $point) {
                                $this->_valuesArray['compare'][] = (int)$point['count'];
                                $lineCompare[] = "['" . $point['period_grouped'] . "'," . (int)$point['count'] . "]";
                            }
                        }
                        $lineCompare[] = "['" . $helper->getDateTo(false, true, true) . "',0]";
                    }
                }
            }
        }
        if ($this->isMonthlyPlot()) { //Set non-exist period points to 0
            if ($lineMain && $lineCompare) {
                foreach ($lineMain as $period => $data) {
                    if (!isset($lineCompare[$period])) {
                        $lineCompare[$period] = 0;
                    }
                }
                foreach ($lineCompare as $period => $data) {
                    if (!isset($lineMain[$period])) {
                        $lineMain[$period] = 0;
                    }
                }
            }
        }
        $lineMain = implode(",", $lineMain);
        $lineCompare = implode(",", $lineCompare);
        if ($lineMain) {
            $widgetData = "[" . $lineMain . ($lineCompare ? "],[" . $lineCompare . "]" : "]");
        } else {
            $widgetData = '';
        }

        return $widgetData;
    }
}

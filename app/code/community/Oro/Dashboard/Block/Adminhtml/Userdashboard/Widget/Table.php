<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Table extends Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Abstract
{
    const LINES_LIMIT = 10;
    const FLAT_VIEW_OPTION = 'flat';

    protected function _prepareLayout()
    {
        $this->setTemplate('oro_dashboard/widget/table.phtml');

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
     * Check if table view is flat
     *
     * @return boolean
     */
    public function isFlatView()
    {
        return ($this->getGroupedByAttribute() === self::FLAT_VIEW_OPTION);
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
     * Get widget group by attribute label
     *
     * @return string
     */
    public function getGroupedByAttributeLabel()
    {
        if ($this->isFlatView()) {
            return $this->__("Date/Time");
        }

        return $this->helper("oro_dashboard")->getAttributeData($this->getGroupedByAttribute())->getAttribute()->getStoreLabel();
    }

    /**
     * Get table line number
     *
     * @return string
     */
    public function getLinesNumber()
    {
        return $this->getWidgetConfig()->getLinesNumber();
    }

    /**
     * Process metric value and adds money or number format
     * @param $value
     * @param $metric
     * @return string
     */
    public function processValue($value, $metric)
    {
        /** @var $helper Oro_Dashboard_Helper_Data */
        $helper = Mage::helper('oro_dashboard');
        if ($helper->isMoneyMetric($metric)){
            return $helper->toMoney($value);
        }

        return $helper->toNumber($value);
    }

    /**
     * Get widget data
     *
     * @return Varien_Object
     */
    public function getWidgetData()
    {
        /** @var $helper Oro_Dashboard_Helper_Data */
        $helper = Mage::helper('oro_dashboard');
        $widgetData = new Varien_Object();
        $metricMain = $this->getMetric();
        $metricCompare = $this->getMetricCompare();
        $metricClass = $helper->getMetricsClass($metricMain);
        $dataMain = array();
        $dataCompare = array();
        $groupedValues = array();
        if ($metricClass) {
            $metric = new $metricClass;
            $metric->setWidget($this);
            $allData = $metric->getData();
            $sum = 0;
            $data = '';
            if ($this->isFlatView()) {
                $data = $metric->getDataForTimeline($this->getLinesNumber(), true);
            } else {
                $data = $metric->getDataForPie($helper->getAttributeData($this->getGroupedByAttribute()), ($this->getLinesNumber() - 1));
            }
            if ($data) {
                foreach ($data as $grouped) {
                    if ($grouped['count'] > 0) {
                        if ($this->isFlatView()) {
                            if ($helper->isHourlyPlot()) {
                                $dataMain[$helper->getStoreDate($grouped['period_grouped'],true)] = $this->processValue(
                                    $grouped['count'], $metricMain);
                            } else {
                                $dataMain[$helper->getLocaleDate($grouped['period_grouped'])] = $this->processValue(
                                    $grouped['count'], $metricMain);
                            }
                            $groupedValues[] = $grouped['period_grouped'];
                        } else {
                            $dataMain[$grouped['group_value']] = $this->processValue(
                                $grouped['count'], $metricMain);
                            $sum += $grouped['count'];
                            $groupedValues[] = "'" . $grouped['group_value'] . "'";
                        }
                    }
                }
            }
            if (!$this->isFlatView()) {
                $dataMain[$this->__("Other")] = $this->processValue($allData - $sum, $metricMain);
            }
        }
        if ($this->getMetricCompare()) {
            $metricClass = $helper->getMetricsClass($metricCompare);
            if ($metricClass) {
                $metric = new $metricClass;
                $metric->setWidget($this);
                $metric->setIsCompare(true);
                $allData = $metric->getData();
                $sum = 0;
                $data = '';
                if ($this->isFlatView()) {
                    $data = $metric->getDataForTimeline($this->getLinesNumber(), true, $groupedValues);
                } else {
                    $data = $metric->getDataForPie($helper->getAttributeData($this->getGroupedByAttribute()), ($this->getLinesNumber()-1), $groupedValues);
                }
                if ($data) {
                    foreach ($data as $grouped) {
                        if ($this->isFlatView()) {
                            if ($helper->isHourlyPlot()) {
                                $dataCompare[$helper->getStoreDate($grouped['period_grouped'],true)] = $this->processValue(
                                    $grouped['count'], $metricCompare);
                            } else {
                                $dataCompare[$helper->getLocaleDate($grouped['period_grouped'])] = $this->processValue(
                                    $grouped['count'], $metricCompare);
                            }
                        } else {
                            $dataCompare[$grouped['group_value']] = $this->processValue(
                                $grouped['count'], $metricCompare);
                            $sum += $grouped['count'];
                        }
                    }
                }
                if (!$this->isFlatView()) {
                    $dataCompare[$this->__("Other")] = $this->processValue($allData - $sum, $metricCompare);
                }
            }
        }
        $widgetData->setDataMain($dataMain);
        $widgetData->setDataCompare($dataCompare);

        return $widgetData;
    }
}

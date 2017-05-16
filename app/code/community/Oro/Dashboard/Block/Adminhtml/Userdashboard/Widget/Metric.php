<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Metric extends Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Abstract
{
    protected function _prepareLayout()
    {
        $this->setTemplate('oro_dashboard/widget/simple.phtml');

        return parent::_prepareLayout();
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
        $metricClass = $helper->getMetricsClass($this->getMetric());
        if ($metricClass) {
            $metric = new $metricClass;
            $metric->setWidget($this);
            if ($helper->isMoneyMetric($this->getMetric())){
                return $helper->toMoney($metric->getData());
            }

            return $helper->toNumber($metric->getData());
        }

        return 0;
    }

    /**
     * Get avg widget data
     *
     * @return string
     */
    public function getAvgWidgetData()
    {
        /** @var $helper Oro_Dashboard_Helper_Data */
        $helper = Mage::helper('oro_dashboard');
        $metricClass = $helper->getMetricsClass($this->getMetric());
        if ($metricClass) {
            $metric = new $metricClass;
            $metric->setWidget($this);
            if ($helper->isMoneyMetric($this->getMetric())){
                return $helper->toMoney($metric->getAvgData());
            }

            return $helper->toNumber($metric->getAvgData());
        }

        return 0;
    }

    /**
     * Check if we count avg data per day
     *
     * @return bool
     */
    public function getAvgIsPerDay()
    {
        /** @var $helper Oro_Dashboard_Helper_Data */
        $helper = Mage::helper('oro_dashboard');
        $dateObjectStart = new Zend_Date($helper->getDateFrom(true));
        $dateObjectEnd = new Zend_Date($helper->getDateTo(true));
        $dateObjectEnd->addHour(1);
        if ((int) $dateObjectEnd->sub($dateObjectStart)->toValue() / 60 / 60 / 24 > 1) {
            return true;
        }

        return false;
    }
}

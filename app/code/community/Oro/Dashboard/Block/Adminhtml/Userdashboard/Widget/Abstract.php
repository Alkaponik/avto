<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Userdashboard_Widget_Abstract extends Mage_Adminhtml_Block_Template
{
    protected $_valuesArray = array();
    const DIFFERENCE_SCALE = 0.1;

    /**
     * Get widget
     *
     * @return Oro_Dashboard_Model_Widget || Varien_Object
     */
    public function getWidget()
    {
        if ($this->getData('widget')) {
            return $this->getData('widget');
        }

        return new Varien_Object();
    }

    /**
     * Get widget config
     *
     * @return Varien_Object
     */
    public function getWidgetConfig()
    {
        if ($this->getWidget()->getId()) {
            return $this->getWidget()->getRawWidgetConfig();
        }

        return new Varien_Object();
    }

    /**
     * Get widget name
     *
     * @return string
     */
    public function getWidgetName()
    {
        return $this->getWidget()->getName();
    }

    /**
     * Get widget metric
     *
     * @return string
     */
    public function getMetric()
    {
        return $this->getWidgetConfig()->getMetric();
    }

    /**
     * Get widget data
     *
     * @return array || int
     */
    public function getWidgetData()
    {
        return 0;
    }

    /**
     * Get min and max values for each group of data
     *
     * @return array
     */
    public function getMinMaxByWidgetData()
    {
        $helper = Mage::helper('oro_dashboard');
        $values = array();

        if (isset($this->_valuesArray['main']) && count($this->_valuesArray['main'])) {
            $values['main'] = array('min' => min($this->_valuesArray['main']), 'max' => max($this->_valuesArray['main']));
        } else {
            $values['main'] = array('min' => false, 'max' => false);
        }

        if ($this->getMetricCompare()) {
            $metricClass = $helper->getMetricsClass($this->getMetricCompare());
            if ($metricClass &&
                isset($this->_valuesArray['compare']) &&
                count($this->_valuesArray['compare'])) {
                $values['compare'] = array('min' => min($this->_valuesArray['compare']), 'max' => max($this->_valuesArray['compare']));
            }
        }

        if (!isset($values['compare'])) {
            $values['compare'] = array('min' => false, 'max' => false);
        }

        return $values;
    }

    /**
     * Show y measure for compare data
     *
     * @return bool
     */
    public function isShowZoomedYMeasure()
    {
        if (!$this->getMetricCompare()) {
            return false;
        }

        $minMaxData = $this->getMinMaxByWidgetData();
        $min = $minMaxData['main']['min'];
        $max = $minMaxData['main']['max'];
        $compareMin = $minMaxData['compare']['min'];
        $compareMax = $minMaxData['compare']['max'];

        if ($min === false || $compareMin === false) {
            return false;
        }

        if ($max >= $compareMax) {
            return (bool)(($compareMax / $max) < self::DIFFERENCE_SCALE);
        } else {
            return (bool)(($max / $compareMax) < self::DIFFERENCE_SCALE);
        }
    }
}

<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Model_Metric_NumberOfCustomers extends Oro_Dashboard_Model_Metric_Abstract
{
    protected $_code = 'number_of_customers';
    private $modelName = 'oro_analytics/customer';

    /**
     * Get widget data for metric
     *
     * @return int
     */
    public function getData()
    {
        return $this->getModel($this->modelName)->getCustomersCount(
            Mage::helper('oro_dashboard')->getDateFrom(true),
            Mage::helper('oro_dashboard')->getDateTo(true),
            Mage::helper('oro_dashboard')->getStore()
        );
    }

    /**
     * Get widget data for timeline
     *
     * @param  int     $limit
     * @param  boolean $sort
     * @param  array   $whereValues
     * @return array
     */
    public function getDataForTimeline($limit = null, $sort = false, $whereValues = array())
    {
        return $this->getModel($this->modelName)->getCustomersData(
            Mage::helper('oro_dashboard')->getDateFrom(true),
            Mage::helper('oro_dashboard')->getDateTo(true),
            Mage::helper('oro_dashboard')->getStore(),
            $limit,
            $sort,
            $whereValues
        );
    }

    /**
     * Get widget data for pie
     * @param  Varien_Object $attributeData
     * @param  int           $limit
     * @param  array         $whereValues
     * @return array
     */
    public function getDataForPie(Varien_Object $attributeData, $limit, $whereValues = array())
    {
        return $this->getModel($this->modelName)->getCustomersGroupData(
            Mage::helper('oro_dashboard')->getDateFrom(true),
            Mage::helper('oro_dashboard')->getDateTo(true),
            $attributeData->getAddressType(),
            $attributeData->getAttribute(),
            Mage::helper('oro_dashboard')->getStore(),
            $limit,
            $whereValues
        );
    }

    /**
     * Get avg widget data for metric
     *
     * @return int
     */
    public function getAvgData()
    {
        return $this->round($this->getModel($this->modelName)->getCustomersAvgCount(
            Mage::helper('oro_dashboard')->getDateFrom(true),
            Mage::helper('oro_dashboard')->getDateTo(true),
            Mage::helper('oro_dashboard')->getStore()
        ));
    }
}

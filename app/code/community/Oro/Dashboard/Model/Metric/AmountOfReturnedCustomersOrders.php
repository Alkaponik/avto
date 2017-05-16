<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Model_Metric_AmountOfReturnedCustomersOrders extends Oro_Dashboard_Model_Metric_Abstract
{
    protected $_code = 'amount_of_returned_customers_orders';
    private $modelName = 'oro_analytics/shop';

    /**
     * Get widget data for metric
     *
     * @return int
     */
    public function getData()
    {
        return $this->getModel($this->modelName)->getReturnedCustomersOrdersAmountCount(
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
        return $this->getModel($this->modelName)->getReturnedCustomersOrdersAmountData(
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
     *
     * @return array
     */
    public function getDataForPie(Varien_Object $attributeData, $limit, $whereValues = array())
    {
        return $this->getModel($this->modelName)->getReturnedCustomersOrdersAmountGroupData(
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
        return $this->round($this->getModel($this->modelName)->getReturnedCustomersOrdersAmountAvgCount(
            Mage::helper('oro_dashboard')->getDateFrom(true),
            Mage::helper('oro_dashboard')->getDateTo(true),
            Mage::helper('oro_dashboard')->getStore()
        ));
    }
}

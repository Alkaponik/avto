<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Testimonial_MageDoc_Model_Retailer_Data_Import_Settings_Rule_Condition_MetricChange
    extends Testimonial_MageDoc_Model_Retailer_Data_Import_Settings_Rule_Condition_Metric
{
    protected $_previousSession = false;

    public function loadAttributeOptions()
    {
        $attributes = array(
            'invalid_records_count' => Mage::helper('magedoc')->__('Invalid records count change percent'),
            'valid_records' => Mage::helper('magedoc')->__('Valid records count change percent'),
            'records_with_old_brands' => Mage::helper('magedoc')->__('Linked to supplier change percent'),
            'records_linked_to_directory' => Mage::helper('magedoc')->__('Linked to directory change percent'),
            'new_brands' => Mage::helper('magedoc')->__('New brands change percent'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    protected function _getMetricValue($metricCode)
    {
        return $this->getSession()->getMetricChangePercent($metricCode);
    }
}

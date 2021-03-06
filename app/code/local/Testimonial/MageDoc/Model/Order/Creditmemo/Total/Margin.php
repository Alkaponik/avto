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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Testimonial_MageDoc_Model_Order_Creditmemo_Total_Margin extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    /**
     * Collect total cost of refunded items
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return Testimonial_MageDoc_Model_Order_Creditmemo_Total_Margin
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $creditmemo->setBaseMargin($creditmemo->getBaseSubtotal()-$creditmemo->getBaseCost());
        $creditmemo->setMargin($creditmemo->getSubtotal()-$creditmemo->getCost());
        return $this;
    }
}

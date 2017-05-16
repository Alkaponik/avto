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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml quote session
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Testimonial_MageDoc_Model_Session_Quote extends Mage_Adminhtml_Model_Session_Quote
{

    public function getQuote()
    {

        if (is_null($this->_quote)) {
            $this->_quote = Mage::getModel('magedoc/quote');
            if ($this->getStoreId() && $this->getQuoteId()) {
                $this->_quote->setStoreId($this->getStoreId())
                    ->load($this->getQuoteId());
            }
            elseif($this->getStoreId() && $this->hasCustomerId()) {
                $quoteCollection = Mage::getResourceModel('sales/quote_collection')
                    ->addFieldToFilter('is_active', 0)
                    ->addFieldToFilter('store_id', $this->getStoreId())
                    ->addFieldToFilter('customer_id', $this->getCustomer()->getId())
                    ->addFieldToFilter('reserved_order_id', array('null' => true))
                    ->setPageSize(1)
                    ->setOrder('entity_id');

                if (!$this->getOrderId()
                    && !$this->getReordered()
                    && $quoteCollection->getFirstItem()->getId()){
                    $this->setQuoteId($quoteCollection->getFirstItem()->getId());
                    $this->_quote->setStoreId($this->getStoreId())
                        ->load($this->getQuoteId());
                } else {
                    $this->_quote->setStoreId($this->getStoreId())
                        ->setCustomerGroupId(Mage::getStoreConfig(self::XML_PATH_DEFAULT_CREATEACCOUNT_GROUP))
                        ->assignCustomer($this->getCustomer())
                        ->setIsActive(false)
                        ->save();
                    $this->setQuoteId($this->_quote->getId());
                }
            }
            $this->_quote->setIgnoreOldQty(true);
            $this->_quote->setIsSuperMode(true);
        }
        return $this->_quote;
    }

    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('magedoc/order');
            if ($this->getOrderId()) {
                $this->_order->load($this->getOrderId());
            }
        }
        return $this->_order;
    }
}

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
 * @category   Phoenix
 * @package    Phoenix_Tax
 * @copyright  Copyright (c) 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */
class Phoenix_Tax_Helper_Data  extends Mage_Tax_Helper_Data {
    public function getRateInfo($product) {
        if($product->getCanShowPrice() !== false) {
            $tax = Mage::helper('tax');
            $productTypeId = $product->getTypeId();

            if (!$product->isSuper()) {
                $request = Mage::getSingleton('tax/calculation')->getRateRequest();
                if ($tax->displayPriceIncludingTax()) {                    
                    $taxInfo = sprintf($this->__('Incl. %1$s%% tax'), Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($product->getTaxClassId())));
                }
                else {
                    $taxInfo = sprintf($this->__('Excl. %1$s%% tax'), Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($product->getTaxClassId())));
                }


                return $taxInfo;

            }
        }
        return '';
    }

    public function getShippingText($product) {
        return  ($product->getTypeId() != 'virtual' && $product->getTypeId() != 'downloadable') ? $this->__('Excl. shipping') : '';
    }
    
}

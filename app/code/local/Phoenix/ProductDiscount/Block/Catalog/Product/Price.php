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
 * @package    Phoenix_ProductDiscount
 * @copyright  Copyright (c) 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_ProductDiscount_Block_Catalog_Product_Price extends  Mage_Catalog_Block_Product_Price
{
    public function getBiggestSaving($product) {
        if ($product->isConfigurable()) {
            $ids = $product->getTypeInstance()->getChildrenIds($product->getId());
            if (is_array($ids) && !empty($ids[0])) {
                $collection = Mage::getResourceModel('catalog/product_collection')
                        ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                        ->addAttributeToSelect('cost')
                        ->addExpressionAttributeToSelect('discount','(({{cost}} - {{price}}) / {{cost}}) * 100', array('price', 'cost'))
                        ->addAttributeToSort('discount', 'desc')
                        ->addAttributeToFilter('entity_id', $ids[0])
                        ->setPage(1,1);
                return round($collection->getFirstItem()->getDiscount(), 0);
            }
        }
        return false;
    }
}

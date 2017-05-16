<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Catalog super product configurable part block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Phoenix_ProductDiscount_Block_Catalog_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable {
    /**
     * Set the module translaton namespace
     */
    public function _construct() {
        $this->setData('module_name', 'Mage_Catalog');
    }
    /**
     * Returns product discount block html
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function getDiscountHtml($product) {
        $this->setTemplate('productdiscount/discount.phtml');
        $this->setProduct($product);
        return $this->_toHtml();
    }

    /**
     * Returns product saving information block html
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function getSavingInformationHtml($product) {
        $this->setTemplate('productdiscount/saving_info.phtml');
        $this->setProduct($product);
        return $this->_toHtml();
    }

    /**
     * Returns product contents information block html
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function getContentsInformationHtml($product) {
        $this->setTemplate('catalog/product/view/type/contents/configurable.phtml');
        $this->setProduct($product);
        return $this->_toHtml();
    }
}

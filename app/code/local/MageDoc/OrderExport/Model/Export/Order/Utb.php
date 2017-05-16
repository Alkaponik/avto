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
 * @package    MageDoc_OrderExport
 * @copyright  Copyright (c) 2011 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */
class MageDoc_OrderExport_Model_Export_Order_Utb extends MageDoc_OrderExport_Model_Export_Order_Abstract
{

    protected $_writeEmptyValues = true;
    
    protected function _initWriteAdapter($fileName)
    {
        $adapter = Mage::getModel('magedoc_orderexport/export_adapter_csv', array($fileName));
        self::log("Export: Strating output to file $fileName");
        return $adapter;
    }

    protected function _exportOrder($order, $writeAdapter)
    {
        foreach ($order->getAllItems() as $item) {
            $this->_writeMappedData($writeAdapter, $item, self::getOrderItemAttributesMap());
        }
        $this->_writeMappedData($writeAdapter, $order, self::getOrderAttributesMap());
        return $this;
    }

    static public function getOrderItemAttributesMap()
    {
        if (!isset(self::$_orderItemAttributesMap)) {
            self::$_orderItemAttributesMap = array(
                '@@skipNext' => '::_isNotItemUtbTypeAllowed(@)',
                'item' => array(
                    '@inrement_id'      =>  '../@increment_id',
                    '@isbn'             =>  '@utb_isbn_utb',
                    '@transaction_id'   =>  '../getPayment()/@last_trans_id',
                    '@qty'              =>  '@qty_ordered',
                    '@price'            =>  '::_calcRowTotal(@row_total_incl_tax,@discount_amount)'
                    )
            );
        }
        return self::$_orderItemAttributesMap;
    }

    static public function getOrderAttributesMap()
    {
        if (!isset(self::$_orderAttributesMap)) {
            self::$_orderAttributesMap = array(
                '@@skipNext' => '::_isShippingFree(@)',
                'item' => array(
                    '@inrement_id'      =>  '@increment_id',
                    '@isbn'             =>  '700008',
                    '@transaction_id'   =>  'getPayment()/@last_trans_id',
                    '@qty'              =>  '1',
                    '@price'            =>  ':round(@shipping_amount, 2)'
                    )
            );
        }
        return self::$_orderAttributesMap;
    }

    protected function _getFilePath($fileName)
    {
        return $this->_getDataDir() . $this->getRelativeExportPath() . $fileName;
    }

    protected function _afterExportOrder($adapter, &$orders)
    {
        if ($this->_getConfigData('create_shipment')){
            foreach($orders as $order){
                $this->_createShipment($order);
            }
        }
        $this->ftpUploadFile();
    }

    protected function _isNotItemUtbTypeAllowed($item)
    {
        return !in_array($item->getUtbType(), Mage::helper('magedoc_orderexport')->getAllowedUtbTypes());
    }

    protected function _isShippingFree($order)
    {
        return $order->getShippingAmount() == 0;
    }
}
<?php

abstract class MageDoc_OrderExport_Model_Export_Order_Abstract extends MageDoc_OrderExport_Model_Abstract
{
    const ORDER_EXPORT_HISTORY_MARKER = 'Order Export';
    const ORDER_ADDRESS_EXPORT_HISTORY_MARKER = 'Order Address Export';
    const ORDER_EXPORT_FILE = 'order_export.xml';
    const ORDER_ADDRESS_EXPORT_FILE = 'address_export.xml';

    /* Binary values are used for orders' matching */
    const ORDER_EXPORT_STATUS_EXPORTED = 1;
    const ORDER_EXPORT_STATUS_ADDRESS_EXPORTED = 2;

    const TEXT_CONTENT_ATTRIBUTE = 'textContent';

    protected $_writeEmptyValues = false;

    protected $_writeAdapter;

    protected $_date;

    protected $_exportedOrdersByStatus = array();

    protected $_customerGroupIds = array();

    protected $_customer;

    protected $_paymentMethodMap;
    
    protected $_salesRules = array();

    static protected $_addressAttributesMap;
    static protected $_orderAttributesMap;
    static protected $_orderItemAttributesMap;
    static protected $_orderShippingAttributesMap;

    protected function _beforeExportOrder($adapter, $oldData)
    {

    }

    /**
     * Start export
     */
    function exportOrder()
    {
        try {
            self::log('Order Export Started');
            $orders = $this->getOrdersToExport(self::ORDER_EXPORT_STATUS_EXPORTED,$this->_getConfigData('order_export_limit'));

            if (!count($orders)) {
                self::log('No orders to export');
                return $this;
            }

            $exportFileName = $this->getOrderExportFilename();
            $exportFileName = $exportFileName ?
                    $exportFileName :
                    self::ORDER_EXPORT_FILE;

            $oldData = $this->_getOldData($exportFileName);

            $write = $this->_getWriteAdapter($exportFileName);

            $this->_beforeExportOrder($write, $oldData);

            foreach ($orders as $order) {
                $this->_exportOrder($order, $write);

                // mark order as exported
                $this->_setOrderExportStatus($order,self::ORDER_EXPORT_STATUS_EXPORTED);
            }
            if (!Mage::helper('magedoc_orderexport')->isSandboxMode()){
                $this->_updateExportedOrderStatuses();
            }

            $this->_afterExportOrder($write, $orders);

            self::log(count($orders)." orders were exported to file: $exportFileName");
        }catch (Exception $e) {
            self::log('Order Export failed: '.$e->getMessage());
            Mage::logException($e);
        }
    }

    protected function _afterExportOrder($adapter, &$orders)
    {

    }

    protected function _exportOrder($order, $writeAdapter)
    {
        $this->_writeMappedData($writeAdapter,$order,self::getOrderAttributesMap());
        foreach ($order->getAllItems() as $item) {
            $this->_writeMappedData($writeAdapter,$item,self::getOrderItemAttributesMap());
        }
        return $this;
    }

    public function exportOrderAddress()
    {
        try {
            self::log('Order Address Export Started');
            $orders = $this->getOrdersToExport(self::ORDER_EXPORT_STATUS_ADDRESS_EXPORTED,$this->_getConfigData('order_address_export_limit'));

            if (!count($orders)) {
                self::log('No order addresses to export');
                return $this;
            }

            $exportFileName = $this->_getConfigData('order_address_export_filename');
            $exportFileName = $exportFileName ?
                    $exportFileName :
                    self::ORDER_ADDRESS_EXPORT_FILE;

            $oldData = $this->_getOldData($exportFileName);

            $write = $this->_getWriteAdapter($exportFileName);
            $write->startDocument('1.0','UTF-8');
            $write->startElement('ADRESSEN');

            if (!empty($oldData)) {
                $write->writeRaw($oldData);
            }

            foreach ($orders as $order) {
                // mark order as exported
                $shippingAddress = $order->getShippingAddress();
                $billingAddress = $order->getBillingAddress();
                $this->_writeMappedData($write,$billingAddress,self::getAddressAttributesMap());

                $isUnique = $this->addressCompare($billingAddress, $shippingAddress);
                $V2AD1003 = self::getAddressAttributesMap();
                unset($V2AD1003['HAUPT']['VS_DIENST']);
                if ($isUnique) {
                    $this->_writeMappedData($write,$shippingAddress,array('V2AD1003' => $V2AD1003['HAUPT']));
                }
                $this->_setOrderExportStatus(
                        $order,
                        self::ORDER_EXPORT_STATUS_ADDRESS_EXPORTED,
                        self::ORDER_ADDRESS_EXPORT_HISTORY_MARKER
                );
            }

            $write->endElement();
            $write->endDocument();

            if (!Mage::helper('magedoc_orderexport')->isSandboxMode()){
                $this->_updateExportedOrderStatuses();
            }

            self::log(count($orders)." order addresses were exported to file: $exportFileName");
        }catch (Exception $e) {
            self::log('Order Address Export failed: '.$e->getMessage());
            Mage::logException($e);
        }
    }

    protected function _getFilePath($fileName)
    {
        return 'file://'.$this->_getDataDir() . $this->getRelativeExportPath() . $fileName;
    }

    protected function _getOldData($exportFileName)
    {
        return null;
    }

    protected function _getWriteAdapter($fileName=null)
    {
        if (!isset($this->_writeAdapter)) {
            if(!$fileName) {
                $fileName = self::ORDER_EXPORT_FILE;
            }
            $io = $this->_getIoObject($this->_getDataDir() . $this->getRelativeExportPath());
            unset($io);
            $fileName = $this->_getFilePath($fileName);

            $writer = $this->_initWriteAdapter($fileName);
            
            $this->_writeAdapter = $writer;
        }
        return $this->_writeAdapter;
    }

    abstract protected function _initWriteAdapter($fileName);

    /**
     * Writes an XML document using $writeAdapter
     * Data is gathered from the $dataObject
     * XML document structure and data mapping is defined by the $dataMap
     * $dataMap has the following format:
     * array(                                       //Document root
     *      'SOME_TAG'  =>  array(                  //Container element
     *              //This is the SOME_TAG element's attribute example
     *              //its value is grabed from the $dataObject using
     *              //getData(data_object_attribute_name) method
     *          '@some_attribute'         => '@data_object_attribute_name',
     *              //This is the previous expression written in other form
     *              // ./ - means current $dataObject (not the original one)
     *          '@some_attribute'         => './@data_object_attribute_name'
     *              //This is the simple tag element example
     *              //its value is gathered from the parent of the $dataObject - ../
     *              //let's assume it is order_address and its parent is order
     *              //then the getPayment() function is called for order
     *              //the result of the call is stored in temporary dataObject named $data
     *              //further the ::decrypt() function of $this (MageDoc_OrderExport_Model_Export_Order_Abstract)
     *              //is called with the parameter @cc_number_enc which is obtained from the
     *              //temprorary dataObject by calling $data->getData('cc_number_enc');
     *              //the result is put into the TAG_ELEMENT
     *          'TAG_ELEMENT'             =>  '../getPayment()/::decrypt(@cc_number_enc)',
     *          'A_NEW_CONTAINER_ELEMENT' =>  array(
     *              //it might be any structure understandable for the parser
     *      ),
     * )
     *
     * @param MageDoc_OrderExport_Model_Export_Adapter_Abstract $writeAdapter
     * @param Varien_Object $dataObject
     * @param array $dataMap
     */

    protected function _writeMappedData($writeAdapter,$dataObject,$dataMap)
    {
        if (empty($dataMap)){
            return $this;
        }
        $level = 0;
        $parentNodes = array($level=>$dataMap);
        $counter = 0;
        do {
            $counter++;
            if ($counter>200) {
                Mage::throwException('MageDoc_OrderExport_Model_Export_Order_Abstract::_writeMappedData(): Infinite Loop!!!');
//                print_r('Infinite Loop!!!');
                //die;
            }
            $node = current($parentNodes[$level]);
            $key = key($parentNodes[$level]);
//            print_r("current_node_key=$key<br/>");
            if (is_array($node)) {
                /* It's a container element */
                $key = explode('__', $key);
                $writeAdapter->startElement($key[0]);
                $level++;
                $parentNodes[$level]=$node;
                $next=current($parentNodes[$level]);
            } else {
                if (strpos($node, '{{') !== false){
                    $data = Mage::helper('magedoc_system')->processTemplate($node, $dataObject, $this);
                } else {
                    $data = Mage::helper('magedoc_system')->executeExpression($dataObject, $node, $this);
                }

                if ((!is_object($data) && !is_array($data) && strlen($data)) || !empty($data) || $this->_writeEmptyValues) {
                    if (substr($key,0,1)=='@') {
                        if (substr($key,0,2)=='@@') {
                            /* processing service call */
                            $action = explode('_',substr($key,2), 2);
                            $methodName = isset($action[1]) ? $action[1] : null;
                            $action = $action[0];
                            switch($action) {
                                case 'include':
                                    $this->_writeMappedData($writeAdapter, $data, $this->$methodName());
                                    break;
                                case 'each':
                                    if ($data instanceof Varien_Data_Collection_Db){
                                        while($item = $data->fetchItem()){
                                            $this->_writeMappedData($writeAdapter, $item, $this->$methodName());
                                        }
                                    } elseif (is_array($data)){
                                        foreach ($data as $item){
                                            $this->_writeMappedData($writeAdapter, $item, $this->$methodName());
                                        }
                                    }
                                    break;
                                case 'test':
                                default:
                                    if ((is_object($data) && !method_exists($data, '__toString')) || is_array($data)){
                                        break;
                                    }
                                    $data = (string)$data;
                                    if ($action != 'test'){
                                        $data = $data ? $action : null;
                                    }
                                    switch($data) {
                                        case 'break':
                                        /** @todo test this case*/
                                            break 3;
                                        case 'continue':
                                            $level--;
                                            if ($level >= 0) {
                                                $writeAdapter->endElement();
                                                $next = next($parentNodes[$level]);
                                            }else {
                                                break 3;
                                            }
                                            if ($next===false) {
                                                break 3;
                                            }
                                            continue 3;
                                        case 'skipNext':
                                            $next = next($parentNodes[$level]);
                                            if ($next = next($parentNodes[$level])) {
                                                $next = prev($parentNodes[$level]);
                                            }else {
                                                if ($level<=0) {
                                                    break 3;
                                                }
                                            }
                                            break;
                                        default:
                                    }
                            }
                        }else {
                            /* adding attribute */
                            $attributeName = substr($key,1);
                            if ($attributeName == self::TEXT_CONTENT_ATTRIBUTE){
                                $writeAdapter->text(htmlspecialchars($data));
                            } else{
                                $writeAdapter->writeAttribute($attributeName, htmlspecialchars($data));
                            }
                        }
                    } elseif ($key) {
                        /* adding tag elemenent */
                        $writeAdapter->writeElement($key, htmlspecialchars($data));
                    }
                }
                $next = next($parentNodes[$level]);
            }
            /* end of current level */
            if ($next===false) {
                $writeAdapter->endElement();
                $level--;
                if ($level >= 0) {
                    $next = next($parentNodes[$level]);
                }
            }
        } while($level > 0);
    }

    static public function getAddressAttributesMap()
    {
        if (!isset(self::$_addressAttributesMap)) {
            self::$_addressAttributesMap = array(
                    'HAUPT'=>array(
                            'NUMMER'    =>  '../@increment_id',
                            '@@test'    =>  '::isShippingAddressTest(@)',
                            'LFD'       =>  '001',
                            'ANREDE'    =>  '::getSex(@prefix)',
                            'TITEL'     =>  '',
                            'VORNAME'   =>  '@firstname',
                            'NAME'      =>  '@lastname',
                            'ZUSATZ1'   =>  '@company',
                            'ZUSATZ2'   =>  '@suffix',
                            'STRASSE'   =>  '@street',
                            'PLZ'       =>  '@postcode',
                            'ORT'       =>  '@city',
                            'TELEFON'   =>  '@telephone',
                            'FAX'       =>  '@fax',
                            'NOTIZEN'   =>  '',
                            'WERBE'     =>  'FALSCH',
                            'SP_U_WERBE'=>  '____',
                            'MYKEY'     =>  '../@increment_id',
                            'GEBURT'    =>  '../::formatDate(@customer_dob)'
                    ),
                    'V2AD1009' => array(
                            '@ID'       =>  '../@increment_id',
                            'E_MAIL'    =>  '../@customer_email',
                            'SB'        =>  '../::getVfgUserGroup(@)',
                            'Telefon_2' =>  '',
                            'MYKEY'     =>  '../@increment_id'
                    ),
                    'V2AD1005' => array(
                            '@ID'       =>  '../@increment_id',
                            'MWST_KENN' =>  'WAHR',
                            'MYKEY'     =>  '../@increment_id'
                    ),
                    '@@test'    =>  '::isDebitPaymentTest(@)',
                    'V2AD1007' => array(
                            '@ID'       =>  '../@increment_id',
                            'NUMMER'    =>  '../@increment_id',
                            'BLZ'       =>  '../getPayment()/::decrypt(@cc_type)',
                            'BANK'      =>  '../getPayment()/::getBankName(@cc_type)',
                            'KONTO'     =>  '../getPayment()/::decrypt(@cc_number_enc)',
                            'INHABER'   =>  '../getPayment()/@cc_owner',
                            'AKTIV'     =>  'WAHR',
                            'MYKEY'     =>  '../@increment_id'
                    )
            );
        }
        return self::$_addressAttributesMap;
    }

    static public function getOrderAttributesMap()
    {
        if (!isset(self::$_orderAttributesMap)) {
            self::$_orderAttributesMap = array(
                    'AUFTRAG'       =>array(
//                            'ADR_NR'        =>  '::getOrderAddressKey(@)',
                            'ADR_NR'        =>  '@increment_id',
                            'AUFTRAG_NR'    =>  '@increment_id',
                            'HERKUNFT'      =>  '4',
                            'TYP'           =>  'WAHR',
                            'DATUM'         =>  '::formatDate(@created_at)',
                            'RECH_ART'      =>  'getPayment()/::mapPaymentMethod(@method)',
                            '@@test'        =>  '::hasDifferentAddressTest(@)',
                            'ALA'           =>  '001',
                            'LFD_LSKK'      =>  '::isDebitPayment(@)',
                            '@@test_1'      =>  '::isPrepaymentTest(@)',
                            'ZAHLART'       =>  '::isPrepayment(@)',
                            'MYKEY'         =>  '::getOrderMykey(@)',
                            'SHOPNUMMER'    =>  '@increment_id',
                            'MEDIACODE'     =>  '::getMediacode(@)',
                            'VS_DIENST'     =>  '::getShippingService(@)',
                            '@@test_2'      =>  '::isIpaymentTest(@)',
                            'KK_AUTORNR'    =>  'getPayment()/@last_trans_id',
                            'FRAGEBOGEN'    =>  '::getQuestionnairePath(@)'
                    ),
                    '@@test'        =>  '::hasCouponCodeTest(@)',
                    'POSITIONEN'    => array(
                            '@ID'           =>  '@increment_id',
                            'AUFTRAG_NR'    =>  '@increment_id',
                            'PROJEKT'       =>  'SY',
                            'ART_NR'        =>  '::getCouponArtNr(@)',
                            'MENGE'         =>  '1',
                            'PREIS'         =>  '::floatToString(@discount_amount)',
                            'MWST'          =>  '0',
                            'MWST_KZ'       =>  '::getCouponTaxRate(@)',
                            //'BEZEICHNG'     =>  '::getDiscountItemName(@)',
                            'BEZEICHNG'     =>  'Gutschein',
                            'GS_NUMMER'     =>  '::strtoupper(@coupon_code)'
                    )
            );
        }
        return self::$_orderAttributesMap;
    }

    static public function getOrderShippingAttributesMap()
    {
        if (!isset(self::$_orderShippingAttributesMap)) {
            self::$_orderShippingAttributesMap = array(
                    //'@@skipNext'    =>  '@shipping_amount',
                    '@@test'        => '::isFreeShippingTest(@)',
                    'POSITIONEN'    => array(
                            '@ID'           =>  '@increment_id',
                            'AUFTRAG_NR'    =>  '@increment_id',
                            'PROJEKT'       =>  'SY',
                            'ART_NR'        =>  '1',
                            'MENGE'         =>  '1,00',
                            'PREIS'         =>  '::floatToString(@shipping_amount)',
                            'MWST'          =>  '0,00',
                            'MWST_KZ'       =>  '00',
                            'BEZEICHNG'     =>  'Versandkosten',
                    )
            );
        }
        return self::$_orderShippingAttributesMap;
    }

    public function getOrdersToExport($excludeExportStatus = null, $limit = null)
    {
        $stores = explode(',', $this->_getConfigData('process_store'));

        $orders = Mage::getResourceModel($this->_getOrdersCollectionResourceModelName());
//            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING);
//                ->addFieldToFilter('store_id', array('in' => $stores))
//                ->addAttributeToSelect('customer_email');

        $hlp = Mage::helper('magedoc_orderexport');
        if ($methods = $hlp->getAllowedPaymentMethods()){
            $orders->getSelect()->join(
                array (
                    'p' => $orders->getResource()->getTable('sales/order_payment')),
                'p.parent_id = main_table.entity_id',
                ''
            );
            $orders->addFieldToFilter('p.method', array('in' => $methods));
        }
        if ($statuses = $hlp->getAllowedOrderStatuses()){
            $orders->addFieldToFilter('status', array('in' => $statuses));
        }
        if ($supplyStatuses = $hlp->getAllowedOrderSupplyStatuses()){
            $orders->addFieldToFilter('supply_status', array('in' => $supplyStatuses));
        }

        if (!is_null($excludeExportStatus)) {
            $orders->getSelect()->where('NOT export_status & ? OR export_status IS NULL', $excludeExportStatus);
        }

        if ($limit) {
            $orders->setPage(1, $limit);
        }
        
        return $orders;
    }

    protected function _getOrdersCollectionResourceModelName()
    {
        return 'sales/order_collection';
    }

    /**
     * Mark orders as exported
     *
     * @params $orderId		int		Order ID that should be marked as exported.
     * @return Phoenix_Logwin_Model_Orders
     */
    protected function _setOrderExportStatus($order, $exportStatus = 0, $historyMarker = self::ORDER_EXPORT_HISTORY_MARKER)
    {
        $exportStatus = $order->getExportStatus()|$exportStatus;
        $order->setExportStatus($exportStatus);
        if (!isset($this->_exportedOrdersByStatus[$exportStatus])) {
            $this->_exportedOrdersByStatus[$exportStatus] = array($order->getId());
        }else {
            $this->_exportedOrdersByStatus[$exportStatus][] = $order->getId();
        }

        //$order->getResource()->isPartialSave(true);die;
        //$order->save();
        /*
        $order->addStatusToHistory(
                $order->getStatus(),
                $historyMarker
                )->save();
         *
        */
        return $this;
    }

    protected function _updateExportedOrderStatuses()
    {
        $resource = Mage::getResourceModel('sales/order');
        $writeAdapter = $resource->getReadConnection();

        foreach ($this->_exportedOrdersByStatus as $status => $orderIds) {
            $writeAdapter->update(
                    $resource->getMainTable(),
                    array('export_status'=>$status),
                    array('entity_id IN (?)' => $orderIds)
            );
        }
        return $this;
    }

    protected function _getMappedStatus($sourceStatus)
    {
        $config = Mage::getSingleton('vfg/config');
        $map = $config->getStatusMapping();
        return !empty($map[$sourceStatus]) ? $map[$sourceStatus] : null;
    }

    public function getDate($date=null)
    {
        if(!isset($this->_date)) {
            $this->_date = Mage::app()->getLocale()->date($date);
        }elseif($date) {
            $this->_date->set($date);
        }
        return $this->_date;
    }

    public function formatDate($date)
    {
        $date = $this->getDate($date);
        return $date->get('yyyyMMdd');
    }

    public function getSex($prefix=null)
    {
        switch($prefix) {
            case 'Herr':
                return '01';
            case 'Frau':
                return '02';
            case 'Firma':
                return '04';
            default:
                return '00';
        }
    }

    public function getVfgUserGroup($order)
    {
        $customerGroupId = $order->getData('customer_group_id');
        $storeId = $order->getStoreId();
        return $this->isPrivateSalesStore($storeId)
                ? 'BR'
                : (in_array($customerGroupId, Mage::helper('vfg')->getAggregatorGroupIds()) ? 'SB' : null);
    }
    
    public function mapTaxPercent($percent=null)
    {
        switch(true) {
            case ((float)$percent == 7):
                return '02';
            case ((float)$percent == 19):
                return '03';
            case ((float)$percent == 10):
                return '08';
            case ((float)$percent == 20):
                return '09';
            default:
                return null;
        }
    }

    public function mapPaymentMethod($paymentMethod)
    {
        if (!isset($this->_paymentMethodMap)) {
            $map = array();
            $config = unserialize(Mage::getStoreConfig('vfg/order/payment_methods_mapping'));
            if (isset($config['method'])) {
                foreach ($config['method'] as $key => $method) {
                    $map[$method] = $config['vfgid'][$key];
                }
            }
            $this->_paymentMethodMap = $map;
        }
        return isset($this->_paymentMethodMap[$paymentMethod]) ?
                $this->_paymentMethodMap[$paymentMethod] :
                null;
    }

    public function isDebitPayment($order)
    {
        if (!$order->getPayment()) {
            return null;
        }
        return $this->mapPaymentMethod($order->getPayment()->getMethod())==4 ?
                'OK' :
                null;
    }

    public function decrypt($data)
    {
        if ($data) {
            return Mage::helper('core')->decrypt($data);
        }
        return $data;
    }

    public function getBankName($blz)
    {
        return Mage::helper('debit')->getBankByBlz($this->decrypt($blz));
    }

    public function isDebitPaymentTest($address)
    {
        if (!$this->isDebitPayment($address->getOrder())) {
            return 'skipNext';
        }
        return null;
    }

    public function getMyKey($address)
    {
        return 220000000 + $address->getId();
    }

    public function getOrderKey($order)
    {
        return 120000000 + $order->getIncrementId();
    }

    public function floatToString($value)
    {
        $value+0.000000000001;
        return str_replace('.',',',substr($value,0,strpos($value,'.')+4));
    }

    public function getOrderAddress($order)
    {
        return $order->getBillingAddress();
    }

    public function isPrepaymentTest($order)
    {
        return $this->mapPaymentMethod($order->getPayment()->getMethod())!=1 ?
                'skipNext' :
                null;
    }

    public function isIpaymentTest($order)
    {
        return strpos($order->getPayment()->getMethod(), 'ipayment')===false ?
                'skipNext' :
                null;
    }

    public function getOrderMykey($order)
    {
        return strpos($order->getPayment()->getMethod(), 'paypal')===false ?
                $order->getIncrementId() :
                $order->getPayment()->getLastTransId();
    }

    public function isFreeShippingTest($order)
    {
        return $order->getShippingAmount() > 0 ? 'skipNext' : null;
    }

    public function isShippingAddressTest($address)
    {
        return $address->getAddressType()!='shipping' ? 'skipNext' : null;
    }

    public function isPrepayment($order)
    {
        if (!$order->getPayment()) {
            return null;
        }
        return $order->getPayment()->getMethod() == 'checkmo' ?
                1 :
                0;
    }
    
    public function getCouponArtNr($order)
    {
        $mediaCode = $this->getMediaCodeModel($order);
        if ($mediaCode) {
            return $mediaCode->getTag();
        }
        else {
            return '';
        }
    }
    
    public function getCouponTaxRate($order)
    {
        $rule = $this->getSalesRule($order);
        if ($rule) {
            return (int)$rule->getTaxRate();
        }
        else {
            return 0;
        }
    }
    
    public function getSalesRule($order)
    {
        if (!$order->getAppliedRuleIds()) {
            return false;
        }
        
        if (!isset($this->_salesRules[$order->getId()])) {
            $this->_salesRules[$order->getId()] = false;
            
            foreach (explode(',', $order->getAppliedRuleIds()) as $ruleId) {
                $ruleTmp = Mage::getModel('salesrule/rule')->load($ruleId);
                if (strpos($ruleTmp->getName(), $order->getCouponCode()) !== false) {
                    $this->_salesRules[$order->getId()] = $ruleTmp;
                }
            }
        }
        
        return $this->_salesRules[$order->getId()];
    }
    
    public function getMediaCodeModel($order)
    {
        if (!isset($this->_mediaCodes[$order->getId()])) {
            $this->_mediaCodes[$order->getId()] = false;
            $rule = $this->getSalesRule($order);
            if ($rule && $rule->getMediaCode()) {
                $mediaCodeTmp = Mage::getModel('vfg/salesRule_mediaCode')->load($rule->getMediaCode(), 'code');
                if ($mediaCodeTmp->getId()) {
                    $this->_mediaCodes[$order->getId()] = $mediaCodeTmp;
                }
            }
        }
        
        return $this->_mediaCodes[$order->getId()];
    }

    public function getARTNR($sku)
    {
        $pos = strpos($sku,'-');
        return $pos ? substr($sku,0,$pos) : $sku;
    }

    public function getPROJEKT($sku)
    {
        $pos = strpos($sku,'-');
        return $pos ? substr($sku,$pos+1) : Mage::getSingleton('vfg/product')->getVfgWMByStoreId($this->getOrigStoreId());
    }

    public function addressCompare($billingAddress=null, $shippingAddress=null)
    {
        $billingStr = $billingAddress->format('oneline');
        $shippingStr = $shippingAddress->format('oneline');

        if (strcmp($billingStr, $shippingStr) === 0) {
            return false;
        }

        return true;
    }

    public function getOrderAddressKey($order)
    {
        $address = $order->getBillingAddress();
        return $address->hasCustomerAddressId() ?
                $address->getCustomerAddressId() :
                $address->getId() + 100000000;
    }

    public function getDiscountAmount($order)
    {
        return $order->getDiscountAmount()*-1;
    }

    public function hasCouponCodeTest($order)
    {
        return is_null($this->mapCouponCode($order->getCouponCode(), $order->getStoreId())) && (strlen($order->getCouponCode()) > 14)
                ? true
                : 'skipNext';
        //return (strlen($order->getCouponCode()) > 15) ? true : 'skipNext';
        //return $order->getDiscountAmount()>0 ? true : 'skipNext';
    }

    public function hasDifferentAddressTest($order)
    {
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        return $this->addressCompare($billingAddress, $shippingAddress) ? null : 'skipNext';
    }

    public function strtoupper($string)
    {
        return strtoupper($string);
    }

    public function getMediacode($order)
    {
        $mediaCode = $this->mapCouponCode($order->getCouponCode(), $order->getStoreId());
        return !is_null($mediaCode)
            ? $mediaCode
            : Mage::getStoreConfig('vfg/order/default_mediacode', $order->getStoreId());
    }

    public function getShippingService($order)
    {
        return $this->isPrivateSalesStore($order->getStoreId()) ? '01' : null;
    }

    public function isPrivateSalesStore($storeId)
    {
        return Mage::getStoreConfigFlag('vfg/settings/is_private_sales_store', $storeId);
    }

    public function mapCouponCode($couponCode, $storeId)
    {
        if ($couponCode) {
            $config = unserialize(Mage::getStoreConfig('vfg/order/mediacode_mapping', $storeId));
            if (isset($config['coupon_code'])) {
                foreach ($config['coupon_code'] as $key => $couponCodeRegexp) {
                    if ($couponCodeRegexp){
                        $couponCodeRegexp = "/$couponCodeRegexp/i";
                        try{
                            if (preg_match($couponCodeRegexp, $couponCode)) {
                                return $config['media_code'][$key];
                            }
                        }catch(Exception $e){
                            Mage::logException($e);
                            self::log("preg_match($couponCodeRegexp, $couponCode) failed");
                        }
                    }
                }
            }
        }
        return null;
    }

    public function getDiscountItemName($order)
    {
        $ruleIds = explode(',',$order->getAppliedRuleIds());
        $name = array();
        foreach ($ruleIds as $ruleId){
            $rule = Mage::getModel('salesrule/rule')->load($ruleId);
            $name[] = $rule->getName();
        }
        return implode(', ', $name);
    }

    protected static function log($message)
    {
        Mage::log($message,null,'order_export_system.log');
    }

    protected function _createShipment($order, $savedQtys = null)
    {
        if (!$order->canShip()) {
            self::log($this->__("Cannot do shipment for the order %s.", $order->getIncrementId()));
            return false;
        }
        if (empty($savedQtys)){
            $savedQtys = $this->_getItemQtys();
        }
        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
        $shipment->register();
        $this->_saveShipment($shipment);
    }

    protected function _getItemQtys(){
        $qtys = array();
        return $qtys;
    }

    /**
     * Save shipment and order in one transaction
     * @param Mage_Sales_Model_Order_Shipment $shipment
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }

    public function calcRowTotal($rowTotal, $discount)
    {
        return round($rowTotal - $discount, 2);
    }

    public function isShippingFree($order)
    {
        return $order->getShippingAmount() == 0;
    }
}
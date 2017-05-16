<?php
class Ak_NovaPoshta_Model_Carrier_NovaPoshta
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    const REDELIVERY_TYPE_CASH               = 2;
    const PAYER_RECIPIENT                    = 0;
    const PAYER_SENDER                       = 1;
    const PAYER_THIRD                        = 2;
    const REDELIVERY_PAYMENT_PAYER_SENDER    = 1;
    const REDELIVERY_PAYMENT_PAYER_RECIPIENT = 2;
    const REDELIVERY_TRUE                    = 1;

    protected $_code = 'novaposhta';
    protected $_dataMapConsignment = array(
        'id'    => 'order_id',
        'np_id' => 'ttn'
    );
    private $_requestData;

    public function getRequestData()
    {
        if (!isset($this->_requestData))
            return $this->_requestData = new Varien_Object();

        return $this->_requestData;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @internal param \Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var $result Mage_Shipping_Model_Rate_Result */
        $result = Mage::getModel('shipping/rate_result');

        $shippingPrice = $this->_getDeliveryPriceByWeight($request->getPackageWeight());
        if ($shippingPrice <= 0) {
            return $result;
        }

        $warehouseId = 1; // dummy warehouse ID
        $warehouseName = 'Склад №1'; // dummy warehouse name

        /** @var $method Mage_Shipping_Model_Rate_Result_Method */
        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier($this->_code)
            ->setCarrierTitle($this->getConfigData('name'))
            ->setMethod('warehouse_' . $warehouseId)
            ->setMethodTitle($warehouseName)
            ->setPrice($shippingPrice)
            ->setCost($shippingPrice);

        $result->append($method);

        return $result;
    }

    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @return array
     */
    protected function _getWeightPriceMap()
    {
        $weightPriceMap = $this->getConfigData('weight_price');
        if (empty($weightPriceMap)) {
            return array();
        }

        return unserialize($weightPriceMap);
    }

    /**
     * @param $packageWeight
     *
     * @return float
     */
    protected function _getDeliveryPriceByWeight($packageWeight)
    {
        $weightPriceMap = $this->_getWeightPriceMap();
        $resultingPrice = 0.00;
        if (empty($weightPriceMap)) {
            return $resultingPrice;
        }

        $minimumWeight = 1000000000;
        foreach ($weightPriceMap as $weightPrice) {
            if ($packageWeight <= $weightPrice['weight'] && $weightPrice['weight'] <= $minimumWeight) {
                $minimumWeight = $weightPrice['weight'];
                $resultingPrice = $weightPrice['price'];
            }
        }

        return $resultingPrice;
    }

    public function getTrackingInfo($number)
    {
        try {

            $import               = Mage::getModel('novaposhta/import');
            $consignment          = Mage::getModel('novaposhta/consignment');
            $trackingStatus       = Mage::getModel('novaposhta/source_tracking_status');
            $trackingStage        = Mage::getModel('novaposhta/source_tracking_stage');
            $trackingState        = Mage::getModel('novaposhta/source_tracking_state');
            $trackingBackDelivery = Mage::getModel('novaposhta/source_tracking_backdelivery');
            $trackingDelivery     = Mage::getModel('novaposhta/source_tracking_delivery');
            $trackingPayer        = Mage::getModel('novaposhta/source_tracking_payer');
            $trackingFormPayment  = Mage::getModel('novaposhta/source_tracking_payment');

            Mage::helper('novaposhta')->log('Start consignment status synchronization.');
            $import->updateConsignment($consignment->loadByTtn($number));
            Mage::helper('novaposhta')->log('End consignment status synchronization.');

            $statusData = Mage::helper('novaposhta')->__($trackingState->getOptionText($consignment->getState())) . '<br/>';

            $statusData .= Mage::helper('novaposhta')->__($trackingStatus->getOptionText($consignment->getStatus())) . '<br/>';
            $statusData .= ($consignment->getStatus() == $trackingStatus::STATUS_SHIPMENT_NOT_RECEIVED) ? "<b>" . Mage::helper('novaposhta')->__("Stage") . "</b>: " .
                    Mage::helper('novaposhta')->__($trackingStage->getOptionText($consignment->getStage())) . '<br/>' : '';
            $statusData .= ($consignment->getBackDelivery()) ? "<b>" . Mage::helper('novaposhta')->__("Back delivery")."</b>: " .
                    Mage::helper('novaposhta')->__($trackingBackDelivery->getOptionText($consignment->getBackDelivery())) . '<br/>': '';
            if ($consignment->getRedelivery()){
                $redelivery = Mage::getModel('novaposhta/consignment');
                Mage::helper('novaposhta')->log('Start consignment status update.');
                $import->updateConsignment($redelivery->loadByTtn($consignment->getRedelivery()));
                Mage::helper('novaposhta')->log('End consignment status update.');
                $statusData .= "<b>" . Mage::helper('novaposhta')->__("Redelivery")."</b>: " . $consignment->getRedelivery() . ' - ';

                $statusData .= Mage::helper('novaposhta')->__($trackingState->getOptionText($redelivery->getState())) . ' <br/>';

                $statusData .= Mage::helper('novaposhta')->__($trackingStatus->getOptionText($redelivery->getStatus())) . ' <br/>';
                $statusData .= ($redelivery->getStatus() == $trackingStatus::STATUS_SHIPMENT_NOT_RECEIVED) ? "<b>" . Mage::helper('novaposhta')->__("Stage") . "</b>: " .
                    Mage::helper('novaposhta')->__($trackingStage->getOptionText($redelivery->getStage())) . '<br/>' : '';
                if($redelivery->getReceiver()){
                    $statusData .= $redelivery->getReceiver() . ' - ' . $redelivery->getCityReceiverRu() . ' - ' . date('Y-m-d', strtotime($redelivery->getDateReceived())) . '<br/>';
                }
            }
            $statusData .= ($consignment->getDeliveryForm()) ? "<b>" . Mage::helper('novaposhta')->__("Delivery form") . "</b>: " .
                    Mage::helper('novaposhta')->__($trackingDelivery->getOptionText($consignment->getDeliveryForm())) . '<br/>' : '';
            $statusData .= ($consignment->getPayer()) ? "<b>" . Mage::helper('novaposhta')->__("Payer") . "</b>: " .
                    Mage::helper('novaposhta')->__($trackingPayer->getOptionText($consignment->getPayer())) . '<br/>' : '';
            $statusData .= ($consignment->getFromPayment()) ? "<b>" . Mage::helper('novaposhta')->__("Form payment") . "</b>: " .
                    Mage::helper('novaposhta')->__($trackingFormPayment->getOptionText($consignment->getFromPayment())) . '<br/>' : '';

            return new Varien_Object(array(
                'tracking'          => $consignment->getData('ttn'),
                'status'            => $statusData,
                'delivery_location' => $consignment->getData('city_receiver_ru') . " " . $consignment->getData('ware_receiver_ru'),
                'deliverydate'      => $consignment->getData('date_received'),
                'deliverytime'      => '00:00:00',
                'shipped_date'      => $consignment->getData('created_at'),
                'recipient'         => $consignment->getData('receiver'),
                'signedby'         => $consignment->getData('receiver'),
                'carrier_title'     => Mage::helper('novaposhta')->getStoreConfig('title')
            ));

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->__toString());
            throw $e;
        }
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * Do request to shipment
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return array
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            Mage::throwException(Mage::helper('novaposhta')->__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }

        foreach ($packages as $packageId => $package) {
            $request->setPackageId($packageId);
            $request->setPackagingType($package['params']['container']);
            $request->setPackageWeight($package['params']['weight']);
            $request->setPackageParams(new Varien_Object($package['params']));
            $request->setPackageItems($package['items']);
        }
        $result = $this->_doShipmentRequest($request);
        $data[]      = array(
            'tracking_number' => $result['ttn'],
            'label_content'   => $result['label_content']
        );

        $response = new Varien_Object(array(
            'info'   => $data
        ));
        if(!empty($result['ttn']) && $request->getOrderShipment()->getOrder()->getShippingCarrier()->getCarrierCode() == 'novaposhta')
        {
            $consignment = Mage::getModel('novaposhta/consignment');
            $consignment->setOrderId($result['order_id']);
            $consignment->setOrderIncrementId($request->getOrderShipment()->getIncrementId());
            $consignment->setShipmentId($request->getOrderShipment()->getEntityId());
            $consignment->setTtn($result['ttn']);

            $consignment->setCitySenderRu($this->getRequestData()->getSenderCity());
            $consignment->setSenderCompany($this->getRequestData()->getSenderCompany());
            $consignment->setSenderAddress($this->getRequestData()->getSenderAddress());
            $consignment->setSenderContact($this->getRequestData()->getSenderContact());
            $consignment->setSenderPhone($this->getRequestData()->getSenderPhone());

            $consignment->setRcptName($this->getRequestData()->getRcptName());
            $consignment->setRcptWarehouse($this->getRequestData()->getRcptWarehouse());
            $consignment->setCityReceiverRu($this->getRequestData()->getRcptCityName());
            $consignment->setCityReceiverRu($this->getRequestData()->getRcptCityName());
            if($this->_getRcptStreetName($request)){
                $consignment->setRcptStreetName($this->getRequestData()->getRcptStreetName());
            }
            $consignment->setReceiver($this->getRequestData()->getRcptContact());
            $consignment->setRcptPhoneNum($this->getRequestData()->getRcptPhoneNum());

            $consignment->setDateDesired(date('Y-m-d', strtotime($request->getOrderShipment()->getCreatedAt())));
            $consignment->setPayer($this->getRequestData()->getPayer());
            $consignment->setFormPayment($this->getRequestData()->getPayType());
            $consignment->setCost($this->getRequestData()->getCost());

            $consignment->setAdditionalInfo($this->getRequestData()->getAdditionalInfo());
            $consignment->setDocuments($this->getRequestData()->getDocuments());
            $consignment->setPackType($this->getRequestData()->getPackType());
            $consignment->setFullDescription($this->getRequestData()->getDescription());
            $consignment->setFloorCount($this->getRequestData()->getFloorCount());
            $consignment->setSaturday($this->getRequestData()->getSaturday());

            if($this->_getRedeliveryType($request)){
                $consignment->setRedeliveryType($this->getRequestData()->getRedeliveryType());
                $consignment->setRedelivery(self::REDELIVERY_TRUE);
                $consignment->setDeliveryInOut($this->getRequestData()->getDeliveryInOut());
                $consignment->setRedeliveryPaymentCity($this->getRequestData()->getRedeliveryPaymentCity());
                $consignment->setRedeliveryPaymentPayer($this->getRequestData()->getRedeliveryPaymentPayer());
            }

            $consignment->setWeight($this->getRequestData()->getWeight());
            $consignment->setLength($this->getRequestData()->getLength());
            $consignment->setWidth($this->getRequestData()->getWidth());
            $consignment->setHeight($this->getRequestData()->getHeight());
//            var_dump($consignment->getData());die;
            $consignment->save();
        }


        return $response;
    }


     /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param Varien_Object $request
     * @return Varien_Object
     */
    protected function _doShipmentRequest(Varien_Object $request)
    {
        $this->_prepareShipmentRequest($request);
        $this->_mapRequestToShipment($request);

        return $this->_doRequest();
    }

        /**
     * Prepare shipment request.
     * Validate and correct request information
     *
     * @param Varien_Object $request
     *
     */
    protected function _prepareShipmentRequest(Varien_Object $request)
    {
        $phonePattern = '/[\s\_\-\(\)]+/';
        $phoneNumber = $request->getShipperContactPhoneNumber();
        $phoneNumber = preg_replace($phonePattern, '', $phoneNumber);
        $request->setShipperContactPhoneNumber($phoneNumber);
        $phoneNumber = $request->getRecipientContactPhoneNumber();
        $phoneNumber = preg_replace($phonePattern, '', $phoneNumber);
        $request->setRecipientContactPhoneNumber($phoneNumber);
    }

    /**
     * Map request to shipment
     *
     * @param Varien_Object $request
     * @return Varien_Object
     */
    protected function _mapRequestToShipment(Varien_Object $request)
    {

        $this->getRequestData();
        //var_dump($this->_getRecipientAddressCity($request));die;
        $this->getRequestData()->setOrderId($request->getOrderShipment()->getOrderId());
        $this->getRequestData()->setDate(date('Y-m-d', strtotime($request->getOrderShipment()->getCreatedAt())));
        $this->getRequestData()->setDateDesired(date('Y-m-d', strtotime($request->getOrderShipment()->getCreatedAt())));//TODO: Понять от куда берется дата желательной доставки?
        
        $this->getRequestData()->setSenderCity($this->_getCityById(Mage::helper('novaposhta')->getSenderCity()));
        $this->getRequestData()->setSenderCompany(Mage::helper('novaposhta')->getSenderCompany());
        $this->getRequestData()->setSenderAddress(Mage::helper('novaposhta')->getSenderAddress());
        $this->getRequestData()->setSenderContact(Mage::helper('novaposhta')->getSenderContact());
        $this->getRequestData()->setSenderPhone(Mage::helper('novaposhta')->getSenderPhone());

        $this->getRequestData()->setRcptCityName($this->_getRecipientAddressCity($request));
        $this->getRequestData()->setRcptName($request->getRecipientContactPersonName());
        $this->getRequestData()->setRcptWarehouse($this->_getRcptWarehouseId($request));
        if($this->_getRcptStreetName($request))
            $this->getRequestData()->setRcptStreetName($this->_getRcptStreetName($request));//TODO: Будет добавлен новый вид доставки. В методе проверять - если это тот вид доставки то брать адрес покупателя, если нет - по номеру склада получить адрес склада
        $this->getRequestData()->setRcptContact($request->getRecipientContactPersonName());
        $this->getRequestData()->setRcptPhoneNum($request->getRecipientContactPhoneNumber());

        $this->getRequestData()->setPayType(Mage::helper('novaposhta')->getPaymentType());
        $this->getRequestData()->setPayer($this->_getPayer($request));
        $this->getRequestData()->setCost($request->getOrderShipment()->getOrder()->getBaseGrandTotal());

        $this->getRequestData()->setAdditionalInfo(Mage::helper('novaposhta')->getAdditionalInfo());
        $this->getRequestData()->setDocuments(Mage::helper('novaposhta')->getDocuments());
        $this->getRequestData()->setPackType(Mage::helper('novaposhta')->getPackType());
        $this->getRequestData()->setDescription(Mage::helper('novaposhta')->getDescription());
        $this->getRequestData()->setFloorCount(Mage::helper('novaposhta')->getFloorCount());
        $this->getRequestData()->setSaturday(Mage::helper('novaposhta')->getSaturdayDelivery());
        
        $this->getRequestData()->setDeliveryAmount($this->_getDeliveryAmount($request));
        
        if($this->_getRedeliveryType($request)){
            $this->getRequestData()->setRedeliveryType($this->_getRedeliveryType($request));
            $this->getRequestData()->setDeliveryInOut($request->getOrderShipment()->getOrder()->getBaseGrandTotal());
            $this->getRequestData()->setRedeliveryPaymentCity($this->_getRedeliveryPaymentCity($request));
            $this->getRequestData()->setRedeliveryPaymentPayer($this->_getRedeliveryPaymentPayer($request));
        }
        
        $packages = $request->getOrderShipment()->getPackages();
        $this->getRequestData()->setWeight($packages[1]['params']['weight']);
        $this->getRequestData()->setLength($packages[1]['params']['length']);
        $this->getRequestData()->setWidth($packages[1]['params']['width']);
        $this->getRequestData()->setHeight($packages[1]['params']['height']);

        $productName = '';
        foreach ($packages[1]['items'] as $package) {
            $productName .= $package['name'] . ', ';
        }

        $this->getRequestData()->setContDescription($productName);
//        var_dump($this->getRequestData()->getData());
        return $this->getRequestData();
    }

    protected function _doRequest()
    {
        $clientApi = Mage::getSingleton('novaposhta/import')->getClientApi();
        $track = $this->_parseXmlResponse($clientApi->createConsignment($this->getRequestData()));
        $track['label_content'] = $this->_parseLableResponse($clientApi->getShippingLabel($track));
        return $track;
    }

    private function _getRcptWarehouseId($request)
    {
        $method = explode('_', $request->getShippingMethod());
        $cnt    = count($method);
        return $method[$cnt - 1];
    }
    
    private function _getRedeliveryType($request)
    {
        $currentMethod = $request->getOrderShipment()->getOrder()->getPayment()->getMethod();
        $redeliveryMethods = explode(',', Mage::helper('novaposhta')->getRedeliveryPaymentMethods());
        foreach ($redeliveryMethods as $method) {
            if($method == $currentMethod)
                return self::REDELIVERY_TYPE_CASH;
        }
        return false;
    }

    private function _getPayer($request)
    {
        if ($this->_getRedeliveryType($request) == self::REDELIVERY_TYPE_CASH){
            return self::PAYER_RECIPIENT;
        }elseif ($this->_isFreeShipmentPaymentMethod($request) &&
                Mage::helper('novaposhta')->getFreeShipmentAmount() <= $request->getOrderShipment()->getOrder()->getBaseGrandTotal()){
            return self::PAYER_SENDER;
        }else{
            return Mage::helper('novaposhta')->getPayer();
        }
    }

    private function _isFreeShipmentPaymentMethod($request)
    {
        $currentMethod = $request->getOrderShipment()->getOrder()->getPayment()->getMethod();
        $freeShipmentMethods = explode(',', Mage::helper('novaposhta')->getFreeShipmentMethod());
        foreach ($freeShipmentMethods as $method) {
            if($method == $currentMethod)
                return true;
        }
        return false;
    }

    private function _getDeliveryAmount($request)
    {
        return false;
    }

    private function _getRedeliveryPaymentPayer($request)
    {
        if ($this->_getRedeliveryType($request) == self::REDELIVERY_TYPE_CASH){
            return self::REDELIVERY_PAYMENT_PAYER_RECIPIENT;
        }  else {
            return self::REDELIVERY_PAYMENT_PAYER_SENDER;
        }
    }

    private function _getRedeliveryPaymentCity($request)
    {
        if($this->_getRedeliveryPaymentPayer($request) == self::REDELIVERY_PAYMENT_PAYER_RECIPIENT)
            return $request->getRecipientAddressCity();
        else
            return $request->getShipperAddressCity();
    }

    private function _getRcptStreetName($request)
    {
        return false;
        return trim($request->getOrderShipment()->getShippingAddress()->getStreet1());
    }

    private function _getCityById($cityId)
    {
        $city = Mage::getModel('novaposhta/city')->load($cityId);
        return $city->getNameRu();
    }

    private function _getRecipientAddressCity($request)
    {
        $warehouse = Mage::getModel('novaposhta/warehouse')->load($this->_getRcptWarehouseId($request));
        return $this->_getCityById($warehouse->getNumberInCity());
    }

        /**
     * Parse xml response and return result
     *
     * @param string $response
     * @return Mage_Shipping_Model_Rate_Result|Varien_Object
     */
    protected function _parseXmlResponse($responseXml)
    {
        $attributes = array();
        if (empty($responseXml)) {
            Mage::throwException(Mage::helper('novaposhta')->__('No Response'));
        }
        try {
            foreach ($responseXml->order->attributes() as $key => $value) {
                $attributes[$key] = $value;
            }
            return $this->_applyConsignmentMap($attributes, $this->_dataMapConsignment);
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->__toString());
            return array();
        }
    }

    protected function _parseLableResponse($response)
    {
        /*Временнаая заглушка*/
        return $response;

        //TODO: Получить картинку из результата запроса
        if (empty($response)) {
            Mage::throwException(Mage::helper('novaposhta')->__('No label Response'));
        }
        try {
            $dom = new DOMDocument();
            @$dom->loadHTML(html_entity_decode($response->getBody()));
            $dom->preserveWhiteSpace = false;
            $imgs = $dom->getElementsByTagName('img');
var_dump($imgs);die;

            return ($imgs->item(0)->nodeValue);
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->__toString());
            return array();
        }
    }
    
    protected function _applyConsignmentMap($newConsignment, $map)
    {
        $result = array();
        foreach ($newConsignment as $key => $value) {
            if (!isset($map[$key])) {
                continue;
            }
            $result[$map[$key]] = addcslashes((string)$value, "\000\n\r\\'\"\032");
        }

        return $result;
    }

}

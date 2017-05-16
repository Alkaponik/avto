<?php
class Ak_NovaPoshta_Model_Api_Client
{
    protected $_httpClient;
    protected $_apiKey;

    public function __construct(array $apiSettings)
    {
        $this->_getHttpClient()->setUri($apiSettings[0]);
        $this->_apiKey = $apiSettings[1];
    }

    /**
     * @return Zend_Http_Client
     */
    protected function _getHttpClient()
    {
        if (!$this->_httpClient) {
            $this->_httpClient = new Zend_Http_Client();
            $this->_httpClient->setMethod(Zend_Http_Client::POST)
                ->setHeaders('Content-Type', 'text/xml');
        }
        return $this->_httpClient;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return bool|SimpleXMLElement
     */
    protected function _sendRequest(SimpleXMLElement $xml, $uri = null)
    {
        try {
            $xml = $xml->asXML();

            Mage::helper('novaposhta')->log('Request XML: ' . $xml);
            if (!is_null($uri)){
                $this->_getHttpClient()->setUri($uri);
            }
            
            $this->_getHttpClient()->setRawData($xml);
            $result = $this->_getHttpClient()->request();
            $result = $result->getBody();

            Mage::helper('novaposhta')->log('Response XML: ' . $result);
            if (empty($result)) {
                return false;
            }

            return new SimpleXMLElement($result);
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->__toString());
            return false;
        }
    }

    /**
     * @return array|SimpleXMLElement
     */
    public function getCityWarehouses()
    {
        try {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><file/>');
            $xml->addChild('auth', $this->_apiKey);
            $xml->addChild('citywarehouses');

            $responseXml = $this->_sendRequest($xml);
            if (!$responseXml) {
            Mage::throwException(Mage::helper('novaposhta')->__('Respons is empty.'));
                 }

            return $responseXml->result->cities->city;
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->__toString());
            return array();
        }
    }

    /**
     * @return array|SimpleXMLElement
     */
    public function getWarehouses()
    {
        try {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><file/>');
            $xml->addChild('auth', $this->_apiKey);
            $xml->addChild('warenhouse');

            $responseXml = $this->_sendRequest($xml);
            if (!$responseXml) {
               Mage::throwException(Mage::helper('novaposhta')->__('Respons is empty.'));
            }

            return $responseXml->result->whs->warenhouse;
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->__toString());
            return array();
        }
    }

    public function getConsignmentStatus($trackNumber)
    {
        Mage::helper('novaposhta')->log("getConsignmentStatus($trackNumber)");
        try{
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><file/>');
            $xml->addChild('auth', $this->_apiKey);
            $track = $xml->addChild('tracking');
            $track->addChild('barcode', $trackNumber);

            $responseXml = $this->_sendRequest($xml);
            Mage::helper('novaposhta')->log($responseXml->asXML());
            if (!$responseXml) {
               Mage::throwException(Mage::helper('novaposhta')->__('Respons is empty.'));
             }

           return $responseXml;
        }catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->__toString());
            return array();
        }
    }

    /**
     * Retrieves document tracking information
     * Support API v2 only
     * https://api.novaposhta.ua/v2.0/xml/
     * @param $trackNumbers
     * @return array|bool|SimpleXMLElement
     */

    public function documentsTracking($trackNumbers)
    {
        if (!is_array($trackNumbers)){
            $trackNumbers = array($trackNumbers);
        }
        Mage::helper('novaposhta')->log("documentsTracking()");
        try{
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><file/>');
            $xml->addChild('apiKey', $this->_apiKey);
            $xml->addChild('calledMethod', 'documentsTracking');
            $xml->addChild('modelName', 'InternetDocument');
            $properties = $xml->addChild('methodProperties');
            $documents = $properties->addChild('Documents');
            foreach ($trackNumbers as $trackNumber){
                $documents->addChild('item', $trackNumber);
            }

            $responseXml = $this->_sendRequest($xml);
            
            if (!$responseXml) {
                Mage::throwException(Mage::helper('novaposhta')->__('Respons is empty.'));
            }
            Mage::helper('novaposhta')->log($responseXml->asXML());

            return $responseXml;
        }catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->__toString());
            return array();
        }
    }

    public function getShippingLabel($track)
    {
//        $url = "http://print.novaposhta.ua/index.php";
//        $params= "r=site/sticker&id={$track['ttn']}&useZebra=&token={$this->_apiKey}";
//        $response = $this->processRequest($url, Zend_Http_Client::GET, $params);
        //TODO: Определится как получать картинку.


        /* Временная заглушка*/
        $data = 'iVBORw0KGgoAAAANSUhEUgAAABwAAAASCAMAAAB/2U7WAAAABl'
               . 'BMVEUAAAD///+l2Z/dAAAASUlEQVR4XqWQUQoAIAxC2/0vXZDr'
               . 'EX4IJTRkb7lobNUStXsB0jIXIAMSsQnWlsV+wULF4Avk9fLq2r'
               . '8a5HSE35Q3eO2XP1A1wQkZSgETvDtKdQAAAABJRU5ErkJggg==';
        $data = base64_decode($data);
        return $data;

        try {
            $image = base64_decode(file_get_contents("http://print.novaposhta.ua/index.php?r=barcode&code={$track['ttn']}&c128&t=C"));
            var_dump($image);
                $img = imagecreatefromstring($image);
                header('Content-Type: image/png');
                imagejpeg($img);
                imagedestroy($img);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
       return $image;
    }

    public function createConsignment(Varien_Object $requestData)
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><file/>');
        $xml->addChild('auth', $this->_apiKey);
        $order = $xml->addChild('order', '');
        $order->addAttribute('order_id', $requestData->getOrderId());

        $order->addAttribute('date', $requestData->getDate());
        $order->addAttribute('date_desired', $requestData->getDateDesired());
        $order->addAttribute('sender_city', $requestData->getSenderCity());
        $order->addAttribute('sender_company', $requestData->getSenderCompany());
        $order->addAttribute('sender_address', $requestData->getSenderAddress());
        $order->addAttribute('sender_contact', $requestData->getSenderContact());
        $order->addAttribute('sender_phone', $requestData->getSenderPhone());

        $order->addAttribute('rcpt_city_name', $requestData->getRcptCityName());
        $order->addAttribute('rcpt_name', $requestData->getRcptName());
        $order->addAttribute('rcpt_warehouse', $requestData->getRcptWarehouse());
        if ($requestData->getRcptStreetName())
            $order->addAttribute('rcpt_street_name', $requestData->getRcptStreetName());

        $order->addAttribute('rcpt_contact', $requestData->getRcptContact());
        $order->addAttribute('rcpt_phone_num', $requestData->getRcptPhoneNum());

        $order->addAttribute('pay_type', $requestData->getPayType());
        $order->addAttribute('payer', $requestData->getPayer());
        $order->addAttribute('cost', $requestData->getCost());

        $order->addAttribute('additional_info', $requestData->getAdditionalInfo());
        $order->addAttribute('documents', $requestData->getDocuments());
        $order->addAttribute('pack_type', $requestData->getPackType());
        $order->addAttribute('description', $requestData->getDescription());
        $order->addAttribute('floor_count', $requestData->getFloorCount());
        $order->addAttribute('saturday', $requestData->getSaturday());

        if ($requestData->getRedeliveryType()){
            $order->addAttribute('redelivery_type', $requestData->getRedeliveryType());
            $order->addAttribute('delivery_in_out', $requestData->getDeliveryInOut());
            $order->addAttribute('redelivery_payment_city', $requestData->getRedeliveryPaymentCity());
            $order->addAttribute('redelivery_payment_payer', $requestData->getRedeliveryPaymentPayer());
        }

        $order->addAttribute('weight', $requestData->getWeight());
        $order->addAttribute('length', $requestData->getLength());
        $order->addAttribute('width', $requestData->getWidth());
        $order->addAttribute('height', $requestData->getHeight());

        $orderCont = $order->addChild('order_cont');
        $orderCont->addAttribute('cont_description', $requestData->getContDescription());

        $responseXml = $this->_sendRequest($xml);
//        echo $responseXml->asXML();die;
        if (!$responseXml) {
            return false;
        }

       return $responseXml;
    }

    protected function _processRequest($url, $method = Zend_Http_Client::POST, $params, $ajax = false, $headers = false)
    {
        $client = $this->_getHttpClient();
        $rawData = '';
        $url_array = parse_url($url);
        $client->setUri($url)
            ->setMethod($method)
            ->setConfig(array('strictredirects' => true, 'encodecookies' => false, 'keepalive' => false))
            ->setHeaders('User-Agent', "Mozilla/5.0 (Windows NT 5.1; rv:6.0.1) Gecko/20100101 Firefox/6.0.1")
            ->setHeaders('Accept', '*/*')
            ->setHeaders("Accept-Encoding", "gzip")
            ->setHeaders('Accept-Language', 'ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3')
            ->setHeaders('Accept-Charset', 'utf-8,windows-1251;q=0.7,*;q=0.7')
            ->setHeaders('Origin:', $url_array['host']);
        if($method == Zend_Http_Client::POST){
            $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/x-www-form-urlencoded; charset=UTF-8');
            if($ajax){
                $client->setHeaders("X-Requested-With", "XMLHttpRequest");
            }
        }

        if(is_array($params)){
            foreach($params as $k => $v){
                $rawData .= !$rawData ? "$k=$v" : "&$k=$v";
            }
        }else{
            $rawData = $params;
        }
        if($method == Zend_Http_Client::GET){
            $url .= "?{$rawData}";
            $client->setUri($url);
        }

        // Additional headers for request product
        if ($headers){
            for ($i = 0, $num = count($headers); $i < $num; $i++) {
                $client->setHeaders($headers);
            }
        }
        $client->setRawData($rawData);
        $response = $client->request();

        return $response;
    }
    
    public function processRequest($url, $params, $method, $ajax = false, $headers = false)
    {
        return $this->_processRequest($url, $params, $method, $ajax, $headers);
    }
}

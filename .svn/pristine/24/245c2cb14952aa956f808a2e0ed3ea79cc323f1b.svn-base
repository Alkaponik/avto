<?php
class Testimonial_Intime_Model_Api_Client
{
    protected $_soapClient;
    protected $_httpClient;
    protected $_apiKey;
    protected $_apiUserId;
    protected $_apiUri;

    public function __construct(array $apiSettings)
    {
        $this->_apiUri = $apiSettings[0];
        $this->_apiKey = $apiSettings[1];
        $this->_apiUserId = $apiSettings[2];
        $this->_getSoapClient();
    }

    /**
     * @return Zend_Http_Client
     */
    protected function _getSoapClient()
    {
        if (!$this->_soapClient) {
            $this->_soapClient = new SoapClient($this->_apiUri, array(
                "trace"              => true,
                'exceptions'         => true,
                'connection_timeout' => 9999,
                'features'           => SOAP_SINGLE_ELEMENT_ARRAYS,
                'soap_version'       => SOAP_1_2));
        }
        return $this->_soapClient;
    }

    protected function _getHttpClient()
    {
        if (!$this->_httpClient){
            $this->_httpClient = new Zend_Http_Client();
        }
        return $this->_httpClient;
    }
/**
 *
 * @param string $method
 * @param string $param
 * @return boolean
 */
    protected function _sendRequest($method)
    {
        try {

            $result = $this->_getSoapClient()->$method();
            
            return $result;
        }
        catch (SoapFault $e) {
            Mage::logException($e);
            Mage::helper('intime')->log("Exception: \n" . $e->__toString());
            return false;
        }
    }

    /**
     * @return array|
     */
    public function getCityWarehouses()
    {
        $response = $this->_sendRequest('GetListCities');
        if (!$response) {
            return array();
        }

        try {
            return $response->result;
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('intime')->log("Exception: \n" . $e->__toString());
            return array();
        }
    }

    /**
     * @return array|SimpleXMLElement
     */
    public function getWarehouses()
    {
        $responseXml = $this->_sendRequest('GetListCitiesExt');
        if (!$responseXml) {
            return array();
        }

        try {
            return $responseXml->result;
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('intime')->log("Exception: \n" . $e->__toString());
            return array();
        }
    }


    public function getConsignmentStatusSoap($trackNumber)
    {
        try{
            $responseXml = $this->_getSoapClient()->CheckTTNNumericalStats(array('Number' => $trackNumber));

            if (!$responseXml) {
                Mage::throwException(Mage::helper('intime')->__('Respons is empty.'));
            }

                Mage::helper('intime')->log((string) $responseXml->result);
                return $responseXml->result;
        }
        catch (Exception $e) {
            Mage::helper('intime')->log("Exception: \n" . $e->__toString());
            Mage::throwException(Mage::helper('intime')->__('Respons is empty. Exception: \n' . $e->__toString()));
        }
    }

    /**
     *
     * @param string $trackNumber
     * @return Zend_Http_Response
     */
    public function getConsignmentStatus($trackNumber)
    {
        try{
            $url = 'http://mytime.intime.ua/modules/global_functions.php';
            $params = array('command' => 'get_info_for_ttn', 'ttn' => urlencode($trackNumber));
            $result = $this->_processRequest($url, Zend_Http_Client::GET, $params);

            if (!$result) {
                Mage::throwException(Mage::helper('intime')->__('Respons is empty.'));
            }
            if (!is_a($result, 'Zend_Http_Response')){
                Mage::throwException(Mage::helper('intime')->__('Respons is not Zend_Http_Response object.'));
            }

            return $result;
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('intime')->log("Exception: \n" . $e->__toString());
            return array();
        }

    }

    public function getShippingLabel($track)
    {
//        $url = "http://print.intime.ua/index.php";
//        $params= "r=site/sticker&id={$track['ttn']}&useZebra=&token={$this->_apiKey}";
//        $response = $this->processRequest($url, Zend_Http_Client::GET, $params);
        //TODO: ОпределитCя как получать картинку.


        /* Временная заглушка*/
        $data = 'iVBORw0KGgoAAAANSUhEUgAAABwAAAASCAMAAAB/2U7WAAAABl'
               . 'BMVEUAAAD///+l2Z/dAAAASUlEQVR4XqWQUQoAIAxC2/0vXZDr'
               . 'EX4IJTRkb7lobNUStXsB0jIXIAMSsQnWlsV+wULF4Avk9fLq2r'
               . '8a5HSE35Q3eO2XP1A1wQkZSgETvDtKdQAAAABJRU5ErkJggg==';
        $data = base64_decode($data);
        return $data;

        try {
            $image = base64_decode(file_get_contents("http://print.intime.ua/index.php?r=barcode&code={$track['ttn']}&c128&t=C"));
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
        $order->addAttribute('rcpt_phone_num', $requestData->getRcptphoneNum());

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

    protected function _processRequest($url, $method = Zend_Http_Client::POST, $params = null, $ajax = false, $headers = false)
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
            ->setHeaders('Accept-Charset', 'utf-8,windows-1251;q=0.7,*;q=0.7');
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

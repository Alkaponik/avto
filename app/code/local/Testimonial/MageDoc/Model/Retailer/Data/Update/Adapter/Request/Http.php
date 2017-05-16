<?php
class Testimonial_MageDoc_Model_Retailer_Data_Update_Adapter_Request_Http
{
    protected $_config;

    public function getConfig()
    {
        return $this->_config;
    }

    public function setConfig($config)
    {
        $this->_config = $config;
        return $this;
    }

    public function loadHTML($html)
    {
        libxml_use_internal_errors(true);
        $doc = new DOMDocument;
        if (!$doc->loadHTML($html)) {
            libxml_clear_errors();
        }
        return $doc;
    }

    public function getRequestData()
    {
        $url    = $this->getConfig()->getSourceUrl();
        $cookie = $this->getConfig()->getCookieExpression();
        $params = $this->getConfig()->getSourceParamString();
        $method = $this->getConfig()->getSourceRequestMethod();
        $ajax   = $this->getConfig()->getIsSourceUseAjax();
        $headers = $this->getHeaderData($this->getConfig()->getRequestParamHeaders());
        $result = $this->_processRequest($url, $cookie, $params, $method, $ajax, $headers);
        return $result->getBody();
    }

    public function auth()
    {
        $cookie = null;
        $url    = $this->getConfig()->getLoginUrl();
        $params = $this->getConfig()->getLoginParamString();
        $method = $this->getConfig()->getLoginRequestMethod();
        $ajax   = $this->getConfig()->getIsLoginUseAjax();
        $headers= $this->getHeaderData($this->getConfig()->getLoginParamHeaders());
        if($this->getConfig()->getIsLoginUseCookie()){
            $cookie = $this->getConfig()->getCookieExpression();
        }
        $data = $this->_processRequest($url, $cookie, $params, $method, $ajax, $headers);
        return $data;
    }

    public function check()
    {
        $url    = $this->getConfig()->getCheckUrl();
        $params = $this->getConfig()->getCheckParamString();
        $method = $this->getConfig()->getCheckRequestMethod();
        $ajax   = $this->getConfig()->getIsCheckUseAjax();
        $cookie = $this->getConfig()->getCookieExpression();
        $result = $this->_processRequest($url, $cookie, $params, $method, $ajax);
        return $result;
    }


    public function processRequest($url, $cookie, $params, $method, $ajax)
    {
        return $this->_processRequest($url, $cookie, $params, $method, $ajax);
    }

    protected function _processRequest($url, $cookie, $params, $method = Zend_Http_Client::POST, $ajax = false, $headers = false)
    {
        $client = new Zend_Http_Client();
        $rawData = '';
        $url_array = parse_url($url);
        $client->setUri($url)
            ->setMethod($method)
            ->setConfig(array('strictredirects' => true, 'encodecookies' => false, 'keepalive' => true))
            ->setHeaders('User-Agent', "Mozilla/5.0 (Windows NT 5.1; rv:6.0.1) Gecko/20100101 Firefox/6.0.1")
            ->setHeaders('Accept', '*/*')
            ->setHeaders("Accept-Encoding", "gzip")
            ->setHeaders('Accept-Language', 'ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3')
            ->setHeaders('Accept-Charset', 'utf-8,windows-1251;q=0.7,*;q=0.7')
            ->setHeaders('Connection', 'keep-alive')
            ->setHeaders('Origin:', $url_array['host'])
            ->setHeaders('Cookie', array($cookie));
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

    public function getHeaderData($headerString) {
            $headersArray = explode(';', $headerString);
        if (is_array($headersArray) && !empty($headersArray[0])){
            return $headersArray;
        }else{
            return false;
        }
    }
}

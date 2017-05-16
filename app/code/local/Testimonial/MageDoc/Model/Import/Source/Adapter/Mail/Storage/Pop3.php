<?php

class Testimonial_MageDoc_Model_Import_Source_Adapter_Mail_Storage_Pop3 extends Zend_Mail_Storage_Pop3
{
    protected $_sourceConfig;
    protected $_params = array('date', 'from', 'subject');

    public function getProtocol()
    {
        return $this->_protocol;
    }

    public function searchMessages($searchParams)
    {
        //$protocol = $this->getProtocol();
        $messages = array();
        $countMessages = $this->countMessages();
        if($countMessages > 0){
            for($i = $countMessages; $i >= 1; $i--){
                $message = $this->getMessage($i);
                if(!$message->isMultipart()){
                    continue;
                }

                if(!empty($searchParams)){
                    foreach($searchParams as $key => $value){
                        $header = $message->getHeader($key, 'string');
                        if($key == 'date') {
                            if($value >= strtotime($header)){
                                break 2;
                            }
                        }else{
                            //$header = iconv_mime_decode($header, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
                            $header = imap_utf8($header);
                            if (mb_strpos($header, $value, 0, 'UTF-8') === false) {
                                continue 2;
                            }
                        }
                    }
                }
                $messages[] = $i;
                break;

            }

            $this->saveDateOfLastExecutedMessage($messages);

        }

        return $messages;
    }

    public function getSearchParams($settings)
    {
        $params = array();
        foreach($this->_params as $item){
            if(empty($settings[$item])){
                continue;
            }

            $params[$item] = $settings[$item];
        }
        return $params;
    }

    public function setSourceConfig($sourceConfig)
    {
        $this->_sourceConfig = $sourceConfig;
    }

    public function getSourceConfig()
    {
        return $this->_sourceConfig;
    }

    public function saveDateOfLastExecutedMessage($messages){
        $messageId = empty($messages)? $this->countMessages(): end($messages);
        $message = $this->getMessage($messageId);
        $dateOfLastExecutedMessage = strtotime($message->getHeader('date', 'string'));
        $sourceConfig = $this->getSourceConfig();
        $settings = $sourceConfig->getSourceSettings();
        $settings['date'] = $dateOfLastExecutedMessage;
        $sourceConfig->setSourceSettings($settings)->save();
    }
}
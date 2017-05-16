<?php

class Testimonial_MageDoc_Model_Import_Source_Adapter_Email extends Varien_Object
{
    const EMAIL_PROTOCOL_IMAP = 'imap';
    const EMAIL_PROTOCOL_POP3 = 'pop3';
    const IMPORT_SOURCE_EMAIL_LOG_FILE_NAME = 'magedoc_import_source_email.log';

    protected $_sourceConfig;
    protected $_storage;
    protected $_content = null;
    protected $_file = null;
    protected $_fileName = null;
    protected $_hasContent = false;
    protected $_paramsMap = array(
        'server_settings' => 'host',
        'email_user_name' => 'user',
        'email_password'  => 'password',
        'email_port'      => 'port',
        'email_ssl'       => 'ssl',
        'folder'          => 'folder',
    );
    protected $_errors = array();

    public function __construct($sourceConfig)
    {
        $this->_sourceConfig = $sourceConfig;
    }

    public function getContent()
    {
        $this->_log('getContent()');
        $settings = $this->_sourceConfig->getSourceSettings();
        $params = $this->_getParams($settings);

        $emailProtocol = $settings['email_protocol'];
        if($this->_storage = Mage::getModel("magedoc/import_source_adapter_mail_storage_$emailProtocol", $params)){
            $this->_storage->setSourceConfig($this->_sourceConfig);
            $searchParams = $this->_storage->getSearchParams($settings);
            $this->_log(Mage::helper('magedoc')->__('Search params:'));
            $this->_log($searchParams);
            $messageIds = $this->_storage->searchMessages($searchParams);
            //$this->_log(Mage::helper('magedoc')->__('Message Ids:'));
            //$this->_log($messageIds);
            if($msgWithAttachment = $this->_getLastMsgWithAttachment($messageIds)){
                if($attachment = $this->_getAttachment($msgWithAttachment)){
                    $this->_content = $attachment;
                    if($emailProtocol == self::EMAIL_PROTOCOL_IMAP){
                        $this->_storage->saveDateOfLastExecutedMessage($msgWithAttachment);
                    }
                } else {
                    $this->_error(Mage::helper('magedoc')->__('No attachment found'));
                }
            } else {
                $this->_error(Mage::helper('magedoc')->__('No last message with attachment found'));
            }
        } else {
            $this->_error(Mage::helper('magedoc')->__('Protocol %s is not supported', $emailProtocol));
        }
        return $this;
    }

    public function hasContent()
    {
        return !is_null($this->_content);
    }

    public function saveContent()
    {
        if($this->hasContent()){
            $localEmail = tempnam(Mage::getConfig()->getOptions()->getTmpDir(), 'MageDoc_Source_Email');
            file_put_contents($localEmail, $this->_content);
            $this->_localFileName = $localEmail;
        }
    }

    public function getFile()
    {
        return array(
            'name' => $this->_fileName,
            'tmp_name' => $this->_localFileName
        );
    }

    public function getFileName()
    {
        return $this->_fileName;
    }

    protected function _getAttachment($msgWithAttachment)
    {
        $this->_log('_getAttachment()');
        $content = null;
        foreach (new RecursiveIteratorIterator($msgWithAttachment) as $part) {
            $headers = $part->getHeaders();
            $this->_log($headers);
            if (!isset($headers['content-disposition'])) {
                continue;
            }
            $headers['content-disposition'] = iconv_mime_decode($headers['content-disposition'], ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
            $headers['content-type'] = iconv_mime_decode($headers['content-type'], ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

            $disposition = Zend_Mime_Decode::splitHeaderField($headers['content-disposition']);
            $type = Zend_Mime_Decode::splitHeaderField($headers['content-type']);
            if(isset($type['name']) && !isset($disposition['filename'])){
                $disposition['filename'] = $type['name'];
            }

            if ($disposition[0] != 'attachment' || !isset($disposition['filename'])) {
                continue;
            }


            $settings = $this->_sourceConfig->getSourceSettings();
            if(!empty($settings['attachment_name_exp'])){
                if(!@mb_eregi($settings['attachment_name_exp'], $disposition['filename'])){
                    $this->_error(Mage::helper('magedoc')->__('Attached filename %s does\'t match expression', $disposition['filename'], $settings['attachment_name_exp']));
                    continue;
                }
            }
            $this->_fileName = $disposition['filename'];

            $content = $part->getContent();
            switch ($headers['content-transfer-encoding']) {
                case '7bit':
                    break;
                case '8bit':
                    $content = quoted_printable_decode(imap_8bit($content));
                    break;
                case 'binary':
                    $content = imap_base64(imap_binary($content));
                    break;
                case 'quoted-printable':
                    $content = quoted_printable_decode($content);
                    break;
                case 'base64':
                    $content = imap_base64($content);
                    break;
            }
            break;
        }
        return $content;
    }

    protected function _getLastMsgWithAttachment($messageIds){
        $msgsWithAttachment = array();
        if(is_array($messageIds)){
            $messageIds = array_reverse($messageIds);
            foreach($messageIds as $msgId) {
                $msg = $this->_storage->getMessage($msgId);
                if ($msg->isMultipart()) {
                    $msgsWithAttachment[$msgId] = $msg;
                    break;
                }
            }
        }
        return reset($msgsWithAttachment);
    }

    protected function _getParams($settings)
    {
        $params = array();
        foreach($settings as $key => $value){
            if(array_key_exists($key, $this->_paramsMap) && $value){
                $params[$this->_paramsMap[$key]] = $value;
            }
        }
        return $params;
    }

    protected function _error($message)
    {
        $this->_errors [] = $message;
        $this->_log($message);
        return $this;
    }

    protected function _log($message)
    {
        Mage::log($message, null, self::IMPORT_SOURCE_EMAIL_LOG_FILE_NAME);
        return $this;
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
<?php

class Testimonial_MageDoc_Model_Import_Source_Adapter_Mail_Storage_Imap extends Zend_Mail_Storage_Imap
{
    const FLAG_UNSEEN   = '\Unseen';

    protected static $_knownFlags = array(
        '\Passed'   => Zend_Mail_Storage::FLAG_PASSED,
        '\Answered' => Zend_Mail_Storage::FLAG_ANSWERED,
        '\Seen'     => Zend_Mail_Storage::FLAG_SEEN,
        '\Unseen'   => self::FLAG_UNSEEN,
        '\Deleted'  => Zend_Mail_Storage::FLAG_DELETED,
        '\Draft'    => Zend_Mail_Storage::FLAG_DRAFT,
        '\Flagged'  => Zend_Mail_Storage::FLAG_FLAGGED);

    protected static $_searchFlags = array(
        '\Recent'   => 'RECENT',
        '\Answered' => 'ANSWERED',
        '\Seen'     => 'SEEN',
        '\Unseen'   => 'UNSEEN',
        '\Deleted'  => 'DELETED',
        '\Draft'    => 'DRAFT',
        '\Flagged'  => 'FLAGGED');

    protected $_sourceConfig;

    public function getProtocol()
    {
        return $this->_protocol;
    }

    public function searchMessages($searchParams)
    {
        return $this->getProtocol()->search($searchParams);
    }

    public function getSearchParams($settings)
    {
        $param = array('CHARSET UTF-8 ALL');
        if(!isset($settings['unseen']) || $settings['unseen'] !== false){
            $param[] = 'UNSEEN';
        }

        if(!empty($settings['from'])){
            $param[] = "FROM {$settings['from']}";
        }

        if(!empty($settings['subject'])){
            $length = strlen($settings['subject']);
            $param[] = "SUBJECT {{$length}}\r\n{$settings['subject']}\r\n";
        }

        if(!empty($settings['content_contains'])){
            $param[] = "TEXT {$settings['content_contains']}";
        }

        if(!empty($settings['date'])){
            $param[] = "BEFORE {$settings['date']}";
        }

        return $param;
    }

    public function setSourceConfig($sourceConfig)
    {
        $this->_sourceConfig = $sourceConfig;
    }

    public function getSourceConfig()
    {
        return $this->_sourceConfig;
    }

    public function saveDateOfLastExecutedMessage($message){
        $dateOfLastExecutedMessage = $message->getHeader('date', 'string');
        $sourceConfig = $this->getSourceConfig();
        $settings = $sourceConfig->getSourceSettings();
        $settings['date'] = $dateOfLastExecutedMessage;
        $sourceConfig->setSourceSettings($settings)->save();
    }
}
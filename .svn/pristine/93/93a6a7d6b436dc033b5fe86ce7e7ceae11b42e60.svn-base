<?php

abstract class MageDoc_OrderExport_Model_Abstract extends Mage_Core_Model_Abstract
{
    const XML_PATH_CONFIG_FTP = 'magedoc_orderexport/ftp';

    const INDEX_PROCESS_CATALOG_PRODUCT_ATTRIBUTE = 1;
	const INDEX_PROCESS_CATALOG_PRODUCT_PRICE = 2;
	const INDEX_PROCESS_CATALOG_URL = 3;
	const INDEX_PROCESS_CATALOG_PRODUCT_FLAT = 4;
	const INDEX_PROCESS_CATALOG_CATEGORY_FLAT = 5;
	const INDEX_PROCESS_CATALOG_CATEGORY_PRODUCT = 6;
	const INDEX_PROCESS_CATALOGSEARCH_FULLTEXT = 7;
	const INDEX_PROCESS_CATALOGINVENTORY_STOCK = 8;

    const LOCK_FILE_TIMEOUT = 5400;

    const FILES_IN_CHARSET = 'UTF-8';
    const FILES_OUT_CHARSET = 'UTF-8';

    protected $_relativeSourcePath = 'in/';
    protected $_relativeExportPath = 'out/';
    protected $_relativeImagePath  = 'images/';
    protected $_relativeBackupPath = 'backup/';

    protected $_dataDirExist;
    protected $_storeMap;

    protected static $_modelInstance;

    protected $_date;

    protected $_isLocked;

    protected $_lockFileHandler = null;

    protected $_lockFileName;

    protected $_orderFilename;

    /**
     * Builds array  of XML object, that should be imported
     *
     * @return <type>
     */
    protected function _readItems($fileTemplate)
    {
        $categoryData = array();
        // get stock files from inbound directory
        $categoryFiles = $this->getResourceFiles($fileTemplate);
        // read and parse stock files
        foreach ($categoryFiles as $file) {
            $categoryData[$file] = $this->readXml($file);
        }
        return $categoryData;
    }
    /**
     * Builds XML object by source file.
     *
     * @return	XML object
     */
    public function readXml ($fileName)
    {
        $fileName = $this->_getDataDir() . $this->_relativeSourcePath . $fileName;
        if (!file_exists($fileName)) {
            throw new Exception('Can\'t read XML file: ' . $fileName);
        }
        $xml = simplexml_load_file($fileName);
        return $xml;
    }
    
	/**
	 * Reads directory for resource files.
	 *
	 * @return	array	Sorted resource files within given path.
	 */
	public function  getResourceFiles($pattern)
	{
        $resourceFiles = array();
		$io = $this->_getIoObject($this->_getDataDir() . $this->_relativeSourcePath );
        $files = $io->ls(Varien_Io_File::GREP_FILES);
        foreach ($files as $file) {
            if (strlen($pattern) != 0) {
                if (preg_match($pattern, $file['text'])) {
                    $resourceFiles[] = $file['text'];
                }
            } else {
                $resourceFiles[] = $file['text'];
            }
        }
        sort($resourceFiles);
        return $resourceFiles;
	}
	/**
	 * Creates an Varien_Io_File object and ensures the requested directory exits.
	 *
	 * @param $path		string	Subdir of data dir (e.g. '/var/logwin'.$path)
	 * @return Varien_Io_File
	 */
	protected function _getIoObject($dir)
	{
	    $io = new Varien_Io_File();
	    $io->setAllowCreateFolders(true)
			->open(array('path'=>$dir));
        return $io;
	}
	/**
	 * Directory for file transfers/resource files in /var/...
	 *
	 * @return	string
	 */
    protected function _getDataDir()
    {
        $prefix = $this->_getConfigData('path_prefix');
    	$dir = Mage::getBaseDir('var') . '/erp/' . $prefix;

    	return $dir;
    }
    /**
     * Move processed file into archive
     * @param <type> $fileName
     * @return
     */
	public function moveInArchive($fileName = '')
	{
		if (empty($fileName))
            return;
        $io = $this->_getIoObject($this->_getDataDir());
        $io->setAllowCreateFolders(true);
        $destinationDir = $io->pwd() . DS . $this->getRelativeBackupPath();
        $io->open(array('path'=>$destinationDir));
        $destinationFile = $destinationDir . Varien_File_Uploader::getNewFileName($destinationDir .  $fileName);
        $io->mv($this->_getDataDir() . DS . $this->getRelativeSourcePath() . $fileName, $destinationFile);
	}

    public function setRelativeSourcePath($path)
    {
        $this->_relativeSourcePath = $path;
    }
    public function getRelativeSourcePath()
    {
        return $this->_relativeSourcePath;
    }
    public function setRelativeBackupPath($path)
    {
        $this->_relativeBackupPath = $path;
    }
    public function getRelativeBackupPath()
    {
        return $this->_relativeBackupPath;
    }
    public function setRelativeExportPath($path)
    {
        $this->_relativeExportPath = $path;
    }
    public function getRelativeExportPath()
    {
        return $this->_relativeExportPath;
    }

    /**
     * Retrieve information from Vfg configuration
     *
     * @param   string $field
     * @return  mixed
     */
    protected function _getConfigData($field, $section = 'settings', $storeId = null) {
        if (is_null($storeId)) {
            $storeId = $this->getOrigStoreId();
        }
        $path = 'magedoc_orderexport/' .  $section . '/'.$field;
        return Mage::getStoreConfig($path, $storeId);
    }

    protected static function log($message)
    {
        Mage::log($message);
    }    

    protected function _parseFloat($value){
        return floatval(str_replace(',','.',str_replace('.','',$value)));
    }

    public function massReindex($processIds)
    {
        Mage::unregister('_singleton/index/indexer');
        $indexer = Mage::getSingleton('index/indexer');

        if (empty($processIds) || !is_array($processIds)) {
            $this->_getSession()->addError(Mage::helper('index')->__('Please select Indexes'));
        } else {
            try {
                foreach ($processIds as $processId) {
                    /* @var $process Mage_Index_Model_Process */
                    $process = $indexer->getProcessById($processId);
                    if ($process) {
                        $process->reindexEverything();
                    }
                }
                $count = count($processIds);
                self::log(
                    Mage::helper('index')->__('Total of %d index(es) have successfully reindexed data', $count)
                );
            }catch(Exception $e){
                Mage::log($e->getMessage());
            }
        }
    }

    public function getOrigStoreId(){
        $store = Mage::registry('orig_store');
        $storeId = $store ? $store->getId() : null;
        return $storeId;
    }

    public function getStoreMap(){
        if (!isset($this->_storeMap)){
            $this->_storeMap = unserialize(Mage::getStoreConfig('vfg/settings/store_mapping'));
        }
        return $this->_storeMap;
    }

    public function cronHandler($schedule){
        $jobCode = $schedule->getJobCode();
        $parts = explode('_',$jobCode);
        if (count($parts)<3){
            Mage::log("Wrong Job Code $jobCode");
            return false;
        }
        $stores = $this->getStoresByCode($parts[1]);
        $method = $parts[2];
        if (!$stores) {
            Mage::log("Wrong store {$parts[1]}");
            return false;;
        }elseif(!method_exists($this, $method)) {
            Mage::log("Method doesn't exist {$method}");
            return false;
        }
        if (!is_array($stores)){
            $stores = array($stores);
        }
        $oldStore = Mage::app()->getStore();
        $i = 0;
        $count = count($stores);
        foreach ($stores as $store) {
            if (!Mage::helper('magedoc_orderexport')->isEnabled($store)){
                continue;
            }
            $i++;
            Mage::log("store_id {$store->getId()}");
            Mage::log("method $method");
            Mage::app()->setCurrentStore($store);
            $archive = $count == $i;
            $this->$method($archive);
        }
        Mage::app()->setCurrentStore($oldStore);
    }
    
    public function getDate(){
        if (!isset($this->_date)){
            $this->_date = Mage::app()->getLocale()->date(time());
        }
        return $this->_date;
    }

    public function sendNotificationEmailMessage($message, $recipientEmail=null)
    {
        if (is_null($recipientEmail)){
            $recipientEmail = $this->getDefaultEmailrecipient();
        }

        if ($recipientEmail && !is_array($recipientEmail)){
            $recipientEmail = array($recipientEmail);
        }

        if (empty($recipientEmail)){
            Mage::log('Unable to send notification email: no recipient specified');
            return $this;
        }

        $sendTo = array();

        foreach ($recipientEmail as $email){
            $sendTo[] = array(
                'email' => $email,
                'name'  => 'Webmaster'
            );
        }

        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setTemplateType(Mage_Core_Model_Email_Template::TYPE_TEXT);
        $mailTemplate->setTemplateSubject('VFG Product Import notification');
        $mailTemplate->setTemplateText($message);
        $storeId = Mage::app()->getStore()->getId();
        $sender = Mage::getStoreConfig(Mage_Log_Model_Cron::XML_PATH_EMAIL_LOG_CLEAN_IDENTITY, $storeId);
        $mailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_'.$sender.'/name', $storeId));
        $mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_'.$sender.'/email', $storeId));
        foreach ($sendTo as $recipient){
            $mailTemplate->send($recipient['email'],$recipient['name']);
        }
    }

    public function getDefaultEmailrecipient()
    {
        if ($recipient = $this->_getConfigData('notification_email_recipient')){
            return explode(';', $recipient);
        }
        return array();
    }

    public function lockFile($fileName = '')
	{
		if (empty($fileName))
            return;
        $io = $this->_getIoObject($this->_getDataDir());
        $io->setAllowCreateFolders(true);
        $destinationDir = $io->pwd() . DS . $this->getRelativeSourcePath();
        $io->open(array('path'=>$destinationDir));
        $destinationFile = $destinationDir . Varien_File_Uploader::getNewFileName($destinationDir .  $fileName . '.lock');
        $io->mv($this->_getDataDir() . DS . $this->getRelativeSourcePath() . $fileName, $destinationFile);
        return $fileName . '.lock';
	}

    public function lock()
    {
        $this->_isLocked = true;

        Mage::register($this->_getLockFileName(), true);

        if (is_file($this->_getLockFileName())) {
            if (!is_null($this->_lockFileHandler)) {
                fclose($this->_lockFileHandler);
                $this->_lockFileHandler = null;
            }
            @unlink($this->_getLockFileName());
        }
        $this->_lockFileHandler = fopen($this->_getLockFileName(), 'w');
        fwrite($this->_lockFileHandler, date('r'));

        return $this;
    }

    protected function _getLockFileName()
    {
        $varDir = Mage::getConfig()->getVarDir('locks');
        return $varDir . DS . $this->_lockFileName;
    }

    public function unlock()
    {
        $this->_isLocked = false;

        Mage::unregister($this->_getLockFileName());

        if (is_file($this->_getLockFileName())) {
            if (!is_null($this->_lockFileHandler)) {
                fclose($this->_lockFileHandler);
                $this->_lockFileHandler = null;
            }
            @unlink($this->_getLockFileName());
        }

        return $this;
    }

    public function isLocked()
    {
        if (isset($this->_isLocked)) {
            return $this->_isLocked;
        } elseif (Mage::registry($this->_getLockFileName()) !== null) {
            return Mage::registry($this->_getLockFileName());
        }
        elseif (is_file($this->_getLockFileName())) {
            if (time()-filemtime($this->_getLockFileName()) > self::LOCK_FILE_TIMEOUT) {
                $this->unlock();
            }else{
                return true;
            }
        }
        return false;
    }

    public function __destruct()
    {
        if ($this->_lockFileHandler) {
            fclose($this->_lockFileHandler);
        }
    }

    public function ftpUploadFile()
    {
        $connection = new Varien_Io_Ftp();
        $config = Mage::getStoreConfig(self::XML_PATH_CONFIG_FTP);
        $connection->open(array(
            'host'     => $config['hostname'],
            'user'     => $config['username'],
            'password' => $config['password'],
            'passive'  => true
        ));
        $connection->cd($config['path']);

        $filesToUpload = $this->_getFilesToUpload();
        
        foreach ($filesToUpload as $alias => $filenamePath) {
            $filename = $filenamePath;
            $localCsv = $this->_getDataDir() . $this->getRelativeExportPath() . $filename;
            Mage::log($localCsv);
            
            if (!is_readable($localCsv)){
                Mage::log(Mage::helper('magedoc_orderexport')->__('Cannot load source file.'));
                continue;
            }
            $dstFilename = basename($alias);
            if ($connection->write($dstFilename, $localCsv)) {
                Mage::log("File transfered successfully $dstFilename");
            }
        }
        $connection->close();
    }

    public function getStoresByCode($code)
    {
        return Mage::app()->getStore($code);
    }

    public function getOrderExportFilename()
    {
        if (!isset($this->_orderFilename)){
            if ($this->_getConfigData('use_current_date_as_filename')){
                $this->_orderFilename = $this->getDate()->toString('YYYY-MM-dd').'.csv';
            }else{
                $this->_orderFilename = $this->_getConfigData('order_export_filename');
            }
            if (!$this->_getConfigData('append_to_existing_file')){
                $this->_orderFilename = 
                        Varien_File_Uploader::getNewFileName($this->_getDataDir() . $this->getRelativeExportPath() .$this->_orderFilename);
            }
        }
        return $this->_orderFilename;
    }

    protected function _getFilesToUpload()
    {
        $filesToUpload = array(
            $this->getOrderExportFilename() => $this->getOrderExportFilename(),
        );
        return $filesToUpload;
    }
}
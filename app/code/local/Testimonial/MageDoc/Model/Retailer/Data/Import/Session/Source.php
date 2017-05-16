<?php
class Testimonial_MageDoc_Model_Retailer_Data_Import_Session_Source extends Mage_Core_Model_Abstract
{
    protected $_uploadBasePath;
    protected $_config = null;
    protected $_session = null;
    protected $_parser = null;
    protected $_sourceAdapter = null;

    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import_session_source');

        $this->_uploadBasePath =  DS . 'magedoc' . DS . 'price';
    }

    public function getConfig()
    {
        if(is_null($this->_config)) {
            $this->_config = Mage::getModel('magedoc/retailer_data_import_adapter_config')->load($this->getConfigId());
        }

        return $this->_config;
    }

    public function setSourceAdapter($sourceAdapter)
    {
        $this->_sourceAdapter = $sourceAdapter;
        return $this;
    }

    public function getSourceAdapter()
    {
        return $this->_sourceAdapter;
    }

    public function uploadSourceFile( $fileName )
    {
        /** @var  $uploader */
        $uploader = $this->getSourceAdapter();
        //$uploader->skipDbProcessing(true);
        $newFileName = Mage::helper('catalog/product_url')->format($fileName);
        $result = $uploader->save($this->getWorkingDir()    , $newFileName);

        $this->moveSourceFile($result);

        return $this;
    }

    public function moveSourceFile( $result )
    {
        $io = $this->_getIoObject($this->getWorkingDir());
        $destinationDir = $io->pwd() . DS;
        $newFilename = Varien_File_Uploader::getNewFileName($destinationDir . $result['file']);
        $destinationFile = $destinationDir . $newFilename;
        $io->mv( $this->getWorkingDir() . DS . $result['file'], $destinationFile );
        $archiver = Mage::getModel('magedoc_system/archiver');

        $unpackPath = $destinationDir . date('Y') . DS . date('m') . DS . time();

        if(!$io->mkdir($unpackPath)) {
            Mage::throwException(Mage::helper('magedoc')->__('Can\'t create directory for file unpack' ));
        }
        if ($archiver->unpack($destinationFile, $unpackPath . DS))
        {
            /**
             * Workaround to get last modified file in directory
             */
            $files = glob("{$unpackPath}" . DS . "*");
            $file = reset($files);

            $newFilename = Mage::helper('catalog/product_url')->format(iconv('cp866', 'utf-8', $file));

            $io->mv($file, $newFilename );

            $this->setData('source_path', $newFilename);

            return $this;
        }

        $this->setData('source_path', $result['path'] . DS . $newFilename);

        return $this;
    }

    public function getWorkingDir()
    {
        return Mage::getBaseDir('var') . $this->_uploadBasePath;
    }

    protected function _getIoObject($dir)
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true)
            ->open(array('path'=>$dir));

        return $io;
    }

    public function getSession()
    {
        if(is_null($this->_session)) {
            $this->_session = Mage::getModel('magedoc/retailer_data_import_session')->load($this->getSessionId());
        }

        return $this->_session;
    }

    public function setSession( Testimonial_MageDoc_Model_Retailer_Data_Import_Session $session)
    {
        $this->_session = $session;
        $this->setSessionId($session->getId());

        return $this;
    }

    public function getParser()
    {
        if(is_null($this->_parser)) {
            $this->_parser = Mage::getModel( $this->getConfig()->getParserModelName(), array('source' => $this) );
        }

        return $this->_parser;
    }

}
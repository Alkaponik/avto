<?php

class Testimonial_MageDoc_Model_Import_Source_Adapter extends Varien_File_Uploader
{
    protected $_fileName = null;
    protected $_sourceAdapterModel;
    const IMPORT_SOURCE_ADAPTER_TYPE = 'global/magedoc/import_source_adapter_type';

    public function __construct($sourceConfig)
    {
        $this->_setUploadFileId($sourceConfig);
        if(!file_exists($this->_file['tmp_name'])) {
            if (isset($this->_sourceAdapterModel) && $errors = $this->_sourceAdapterModel->getErrors()){
                Mage::throwException(Mage::helper('magedoc')->__('File was not uploaded (%s)', implode(', ', $errors)));
            } else {
                Mage::throwException(Mage::helper('magedoc')->__('File was not uploaded'));
            }
        } else {
            $this->_fileExists = true;
        }
    }

    protected function _setUploadFileId($sourceConfig)
    {
        $sourceType = $sourceConfig->getSourceType();

        $adapterClass = Mage::getConfig()->getNode(self::IMPORT_SOURCE_ADAPTER_TYPE . '/' . $sourceType . '/' . 'class');
        if($sourceAdapter = Mage::getModel($adapterClass, $sourceConfig)){
            $this->_sourceAdapterModel = $sourceAdapter;
            $sourceAdapter->getContent();
            if($sourceAdapter->hasContent()){
                $sourceAdapter->saveContent();
                $this->_file = $sourceAdapter->getFile();
                $this->_fileName = $sourceAdapter->getFileName();
            }
        } else {
            Mage::throwException(Mage::helper('magedoc')->__('Unable to initialize source adapter %s', $adapterClass));
        }
    }

    public function getFileName()
    {
        return $this->_fileName;
    }

    protected function _moveFile($tmpPath, $destPath)
    {
        $io = $this->_getIoObject($this->getWorkingDir());
        return $io->mv($tmpPath, $destPath);
    }

    protected function _getIoObject($dir)
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true)
            ->open(array('path'=>$dir));

        return $io;
    }

    public function getWorkingDir()
    {
        return Mage::getBaseDir('var') . DS . 'magedoc' . DS . 'price';
    }

}
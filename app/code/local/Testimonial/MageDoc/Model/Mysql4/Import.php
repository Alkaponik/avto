<?php

class Testimonial_MageDoc_Model_Mysql4_Import extends Mage_ImportExport_Model_Import
{
    const DEFAULT_RETAILER = 0;

    protected $_importParams = array();
    protected $_importIds = array();
    protected $_retailerId;
    protected $_supplierId;
    protected $_categoryId;
    protected $_importStatus = null;

    public function _construct()
    {
        $this->_init('magedoc/import_tmp');
    }
    
    public function importSource()
    {
        $this->setData(array(
            'entity'   => 'catalog_product',
            'behavior' => 'append'
        ));
        
        $this->_getEntityAdapter()->setRetailerId($this->getRetailerId())
                ->setSupplierId($this->getSupplierId())
                ->setCategoryId($this->getCategoryId())
                ->setImportStatus($this->getImportStatus())
                ->setImportIds($this->_importIds);
        $result = $this->_getEntityAdapter()->importData();
        return $result;
    }

    public function setImportStatus($status = false)
    {
        $this->_importStatus = $status;
        return $this;
    }
    
    public function getImportStatus()
    {
        return $this->_importStatus;  
    }
    
    public function setRetailerId($retialerId)
    {
        $this->_retailerId = $retialerId;
        return $this;
    }
    
    public function getRetailerId()
    {
        if(!isset($this->_retailerId)){
            $this->_retailerId = self::DEFAULT_RETAILER;
        }
        return $this->_retailerId;
    }

    public function setCategoryId($categoryId)
    {
        $this->_categoryId = $categoryId;
        return $this;
    }
    
    public function getCategoryId()
    {
        if(!isset($this->_categoryId)){
            $this->_categoryId = null;
        }
        return $this->_categoryId;
    }

    public function setSupplierId($supplierId)
    {
        $this->_supplierId = $supplierId;
        return $this;
    }
    
    public function getSupplierId()
    {
        if(!isset($this->_supplierId)){
            $this->_supplierId = null;
        }
        return $this->_supplierId;
    }

    public function setImportIds(array $importIds)
    {
        $this->_importIds = $importIds;
        return $this;
    }
    
    public function getUpdatedRowsCount()
    {
        return $this->_getEntityAdapter()->getUpdatedRowsCount();
    }

    public function getImportedRowsCount()
    {
        return $this->_getEntityAdapter()->getImportedRowsCount();
    }

    public function getErrorMessages()
    {
        $messages = array();
        foreach ($this->getErrors() as $errorCode => $rows) {
            $error = $errorCode . ' '
                . Mage::helper('importexport')->__('in rows') . ': '
                . implode(', ', $rows);
            $messages[] = $error;
        }
        if($this->_getEntityAdapter()->getErrorsCount()){
            $messages[] = Mage::helper('importexport')->__('Error count: ') 
                    . $this->_getEntityAdapter()->getErrorsCount();
        }
        return implode('<br/>', $messages);
    }
    
}


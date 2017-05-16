<?php
class Testimonial_MageDoc_Adminhtml_PriceController extends Mage_Adminhtml_Controller_Action
{
    protected $_importIds = array();
    protected $_importParams = array();
    protected $_directory;
    
	protected function _initAction()
    {
		$this->loadLayout()
			->_setActiveMenu('magedoc/prices')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Prices'), Mage::helper('adminhtml')->__('Manage Prices'));
		
		return $this;
	}   
 
	public function indexAction() 
    {
        $this->_initDirectory();
		$this->_initAction()
			->renderLayout();
	}    
    
    
    protected function _preparePriceData()
    {
        $id    = $this->getRequest()->getParam('id');
		$model = Mage::getModel('magedoc/import_retailer_data')->load($id);
        if ($model->getId()) {
             $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
             if (empty($data)) {
                  $data = $model->getData();
             }
             $model->addData($data);
        }
        Mage::register('price_data', $model);
        
        return $model;
    }
    
    public function editAction()
    {
        $this->_preparePriceData();
        $this->loadLayout();
        $this->_setActiveMenu('magedoc/prices');

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Prices Manager'), Mage::helper('adminhtml')->__('Prices Manager'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('magedoc/adminhtml_price_edit'))
            ->_addLeft($this->getLayout()->createBlock('magedoc/adminhtml_price_edit_tabs'));

        $this->renderLayout();      
    }
    
    public function newAction()
    {
        $this->_forward('edit');
    }

    
    public function saveAction() 
    {        
        $data = $this->_preparePriceData();
		if($requestData = $this->getRequest()->getPost()){
			$data->addData($requestData);            
            $data->setQty($data->getDomesticStockQty() + $data->getGeneralStockQty());
			$data->save();
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Retailer was successfully saved'));
			Mage::getSingleton('adminhtml/session')->setFormData(false);

			if ($this->getRequest()->getParam('back')) {
				$this->_redirect('*/*/edit', array('id' => $data->getId()));
				return;
			}
            $this->_redirect('*/*/');
			return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}

    
    protected function _initParams()
    {
        if($importIds = $this->getRequest()->getParam('magedoc')){
            if (!is_array($importIds)){
                $this->_importIds = explode(',', $importIds);
            }elseif (count($importIds) > 1 || !empty($importIds[0])){
                $this->_importIds = $importIds;
            }
        }

        if ($filterParam = $this->getRequest()->getParam('filter')){
            $importParams = Mage::helper('adminhtml')->prepareFilterString($filterParam);
        }else{
            Mage::throwException(
                    Mage::helper('magedoc')->__('Please select category'));
        }
        $this->_importParams['retailer'] = isset($importParams['retailer']) ? 
                $importParams['retailer'] : null;
        $this->_importParams['supplier'] = isset($importParams['supplier']) ? 
                $importParams['supplier'] : null;
        $this->_importParams['category'] = isset($importParams['category']) ? 
                $importParams['category'] : null;
        $this->_importParams['is_imported'] = isset($importParams['is_imported']) ? 
                $importParams['is_imported'] : null;

        return $this; 
    }
    
    
    public function massImportAction()
    {
        try {
            $this->_initParams();
            $importModel = $this->_getImportModel();

            $importModel->setRetailerId($this->_importParams['retailer'])
                ->setSupplierId($this->_importParams['supplier'])
                ->setCategoryId($this->_importParams['category'])
                ->setImportStatus($this->_importParams['is_imported'])
                ->setImportIds($this->_importIds);
            $result = $importModel->importSource();
            
            if($result && ($importModel->getProcessedRowsCount() > $importModel->getInvalidRowsCount())){
                $updatedRowsCount = $importModel->getUpdatedRowsCount();
                $importedRowsCount = $importModel->getImportedRowsCount();
                if ($importedRowsCount > 0 || $updatedRowsCount > 0){
                    $importModel->invalidateIndex();
                }
                $this->_getSession()->addSuccess(
                $this->__("Total of %d record(s) was imported <br/> Total of %d record(s) was updated", $importedRowsCount, $updatedRowsCount));
                if($importModel->getInvalidRowsCount()){
                    $this->_getSession()->addError($importModel->getErrorMessages());
                }
            } elseif(($importModel->getProcessedRowsCount() == $importModel->getInvalidRowsCount())) {
                $this->_getSession()->addError($importModel->getErrorMessages());
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/index', array('_current' => true));
    }


    public function massUpdateAction()
    {
        try {
            $this->_initParams();
            $importModel = $this->_getUpdateModel();
            $importModel->setRetailerId($this->_importParams['retailer'])
                ->setSupplierId($this->_importParams['supplier'])
                ->setCategoryId($this->_importParams['category'])
                ->setImportIds($this->_importIds);
            $result = $importModel->importSource();
            
            if($result && ($importModel->getProcessedRowsCount() > $importModel->getInvalidRowsCount())){
                $updatedRowsCount = $importModel->getUpdatedRowsCount();
                if ($updatedRowsCount > 0){
                    $importModel->invalidateIndex();
                }
                $this->_getSession()->addSuccess(
                $this->__("Total of %d record(s) was updated", $updatedRowsCount));
                if($importModel->getInvalidRowsCount()){
                    $this->_getSession()->addError($importModel->getErrorMessages());
                }
            } elseif(($importModel->getProcessedRowsCount() == $importModel->getInvalidRowsCount())) {
                $this->_getSession()->addError($importModel->getErrorMessages());
            }

        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/*/index', array('_current' => true));
    }

    public function retailerbaseAction() {
        $this->loadLayout();

        $this->renderLayout();
    }

    protected function _getImportModel()
    {
        $directory = $this->_initDirectory();
        return $directory->getPriceImportModel();
    }

    protected function _getUpdateModel()
    {
        $directory = $this->_initDirectory();
        return $directory->getPriceUpdateModel();
    }

    protected function _initDirectory()
    {
        if (!isset($this->_directory)){
            $directoryCode = $this->getRequest()->getParam('directory');
            $this->_directory = Mage::getSingleton('magedoc/directory')->getDirectory($directoryCode);
            Mage::register('magedoc_directory', $this->_directory);
        }
        return $this->_directory;
    }

    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'index':
                $aclResource = 'magedoc/price/prices';
                break;
            case 'retailerbase':
                $aclResource = 'magedoc/price/retailerbase';
                break;
            default:
                $aclResource = 'magedoc/price';
                break;

        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }
}
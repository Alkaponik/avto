<?php
class Testimonial_MageDoc_Adminhtml_RetailerController extends Mage_Adminhtml_Controller_action
{
    /**
     * @returns Testimonial_MageDoc_Model_Retailer
     */
    protected function _initRetailer()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var Testimonial_MageDoc_Model_Retailer $retailer */
        $retailer = Mage::getModel('magedoc/retailer')->load($id);

        $data = Mage::getSingleton('adminhtml/session')->getRetailerData(true);
        if (!empty($data['retailer'])) {
            $retailer->addData($data['retailer']);
            $this->_initRetailerImportAdapterConfigs($retailer, $data);
            $this->_initRetailerImportSourceConfigs($retailer, $data);
            $this->_initRetailerImportPriceCrontab($retailer, $data);
        } else {
            $this->_initRetailerImportAdapterConfigs($retailer, $this->getRequest()->getPost());
            $this->_initRetailerImportSourceConfigs($retailer, $this->getRequest()->getPost());
            $this->_initRetailerImportPriceCrontab($retailer, $this->getRequest()->getPost());
        }

        $this->_initRetailerAdapterConfig($retailer);
        $this->_initRetailerSourceConfig($retailer);
        $this->_initRetailerPriceSchedule($retailer);

        if (isset($data['retailer_config_supply']))
        {
            $retailer->getSupplyConfig()->addData($data['retailer_config_supply']);
            $retailer->getSupplyConfig()->setOrderHoursEndFormatted( $data['retailer_config_supply']['order_hours_end_formatted'] );
        }

        Mage::register('retailer', $retailer);
        return $retailer;
    }

    protected function _initRetailerAdapterConfig($retailer)
    {
        $data = $this->getRequest()->getPost();
        if (isset($data['retailer']['import_adapter_config'])) {
            $retailerImportAdapterConfig = Mage::getModel('magedoc/retailer_data_import_adapter_config')
                ->load($data['retailer']['import_adapter_config']);
            $retailer->setImportAdapterConfigModel($retailerImportAdapterConfig);
        } else {
            $retailer->setImportAdapterConfigModel(false);
        }
    }

    protected function _initRetailerImportAdapterConfigs($retailer, $data)
    {
        $adapterConfigModelCollection = $retailer->getImportConfigCollection();
        if (!isset($data['retailer_adapter_config'])){
            return $adapterConfigModelCollection;
        }

        $data = $data['retailer_adapter_config'];

        unset($data['_template_']);

        foreach ($adapterConfigModelCollection as $config) {
            if (!isset($data[$config->getConfigId()])) {
                $config->delete();
            }
        }

        foreach ($data as $id => $config) {
            $adapterConfigModel = $adapterConfigModelCollection->getItemById($id);

            if (empty($adapterConfigModel)){
                $adapterConfigModel = Mage::getModel('magedoc/retailer_data_import_adapter_config');
                $retailer->addImportConfig($adapterConfigModel);
            }
            unset($config['source_adapter_config']['__empty']);
            $adapterConfigModel->addData($config);

            $fieldsToCheck = array('source_adapter_config', 'source_fields_map', 'source_fields_filters');
            foreach($fieldsToCheck as $item){
                if(!isset($config[$item]) && isset($adapterConfigModel[$item])){
                    unset($adapterConfigModel[$item]);
                }
            }
        }
        return $adapterConfigModelCollection;
    }

    protected function _initRetailerSourceConfig($retailer)
    {
        $data = $this->getRequest()->getPost();
        if (isset($data['retailer']['import_source_config'])) {
            $retailerImportSourceConfig = Mage::getModel('magedoc/retailer_data_import_source_config')
                ->load($data['retailer']['import_source_config']);
            $retailer->setImportSourceConfigModel($retailerImportSourceConfig);
        } else {
            $retailer->setImportSourceConfigModel(false);
        }
    }

    protected function _initRetailerPriceSchedule($retailer)
    {
        $data = $this->getRequest()->getPost();
        if (isset($data['retailer']['import_schedule'])) {
            //$retailerPriceSchedule = Mage::getModel('magedoc/retailer_data_price_upload_crontab')
            $retailerPriceSchedule = Mage::getModel('magedoc_scheduler/crontab')
                ->load($data['retailer']['import_schedule']);
            $retailer->setPriceSchedule($retailerPriceSchedule);
        } else {
            $retailer->setPriceSchedule(false);
        }
    }

    protected function _initRetailerImportSourceConfigs($retailer, $data)
    {
        $sourceConfigModelCollection = $retailer->getImportSourceCollection();
        if (!isset($data['retailer_source_config'])){
            return $sourceConfigModelCollection;
        }

        $data = $data['retailer_source_config'];

        unset($data['_template_']);

        foreach ($sourceConfigModelCollection as $config) {
            if (!isset($data[$config->getSourceId()])) {
                $config->delete();
            }
        }

        foreach ($data as $id => $config) {
            $sourceConfigModel = $sourceConfigModelCollection->getItemById($id);

            if (empty($sourceConfigModel)){
                $sourceConfigModel = Mage::getModel('magedoc/retailer_data_import_source_config');
                $retailer->addImportSource($sourceConfigModel);
            }
            unset($config['source_adapter_config']['__empty']);
            foreach($config[$config['source_type']] as $key => $value){
                $value = trim($value);
                $config[$config['source_type']][$key] = $value;
                $config[$key] = $value;
            }
            if(!isset($config[$config['source_type']]['unseen'])){
                $config[$config['source_type']]['unseen'] = false;
            }
            $config['source_settings'] = $config[$config['source_type']];
            unset($config[$config['source_type']]);
            $sourceConfigModel->addData($config);
        }
        return $sourceConfigModelCollection;
    }

    protected function _initRetailerImportPriceCrontab($retailer, $data)
    {
        $priceCrontabModelCollection = $retailer->getPriceCrontabCollection();
        if (isset($data['retailer']) && !isset($data['import_schedule'])
            && $priceCrontabModelCollection->getSize()){
                foreach ($priceCrontabModelCollection as $crontab) {
                    $crontab->delete();
                }
                return array();
        }

        if (!isset($data['import_schedule'])){
            return $priceCrontabModelCollection;
        }

        $data = $data['import_schedule'];

        //unset($data['_template_']);

        foreach ($priceCrontabModelCollection as $crontab) {
            if (!isset($data[$crontab->getCrontabId()])) {
                $crontab->delete();
            }
        }

        foreach ($data as $crontabId => $crontab) {
            $priceCrontabModel = $priceCrontabModelCollection->getItemById($crontabId);

            if (empty($priceCrontabModel)){
                //$priceCrontabModel = Mage::getModel('magedoc/retailer_data_price_upload_crontab');
                $priceCrontabModel = Mage::getModel('magedoc_scheduler/crontab');
                $retailer->addPriceCrontab($priceCrontabModel);
            }
            unset($crontab['import_schedule']['__empty']);

            $parameters = array();

            $parameters['source_id'] = !empty($crontab['source_id'])? $crontab['source_id']: null;
            $parameters['adapter_ids'] = !empty($crontab['adapter_ids'])? $crontab['adapter_ids']: null;
            $parameters['start_new_session'] = isset($crontab['start_new_session'])? 1: 0;
            $parameters['import_brands'] = isset($crontab['import_brands'])? 1: 0;
            $parameters['reference_name'] = $retailer->getName();
            //$priceCrontabModel->addData($crontab);
            $priceCrontabModel->setSchedule($crontab['schedule']);
            $priceCrontabModel->setParameters($parameters);
            $priceCrontabModel->setModel('magedoc/retailer_data_import');
            $priceCrontabModel->setMethod('executeImportSchedule');
            $priceCrontabModel->setJobType('retailer_data_price_upload');
            $priceCrontabModel->setReferenceType('magedoc/retailer');
        }
        return $priceCrontabModelCollection;
    }


    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('magedoc/retailers')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Manage Retailers'), Mage::helper('adminhtml')->__('Manage Retailers')
            );

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();

    }

    public function editAction()
    {
        try {
            $retailer = $this->_initRetailer();

            if ($retailer->isObjectNew()) {
                $retailer
                    ->setRate(1)
                    ->setEnabled(true)
                    ->setStockStatus(Testimonial_MageDoc_Model_Retailer::STOCK_STATUS_IN_STOCK)
                    ->setPriceValidityTerm(Testimonial_MageDoc_Model_Retailer::ACTUAL_PRICE_TERM)
                    ->setMarginRatio(1);
            }

            $lastRetailerSession = $retailer->getLastSession();

            if($lastRetailerSession && !$lastRetailerSession->isObjectNew() && is_null($this->getRequest()->getParam('directory'))) {
                $directoryCode = Mage::getModel('magedoc/retailer_data_import')
                    ->setRetailer($retailer)
                    ->getDirectoryCode($retailer->getLastSession()->getLastSource());

                $this->getRequest()->setParam('directory', $directoryCode);
            }

            $this->loadLayout();
            $this->_setActiveMenu('magedoc/retailers');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Retailers Manager'), Mage::helper('adminhtml')->__('Retailers Manager')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit'))
                ->_addLeft($this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit_tabs'));

            $this->renderLayout();
        //} catch (Testimonial_MageDoc_Model_Retailer_Data_Import_Session_Exception $e){
        } catch (Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to load retailer: %s', $e->getMessage()));
            if (($retailer = Mage::registry('retailer')) && $retailer->getActiveSession()->getId()){
                Mage::getSingleton('adminhtml/session')->addNotice($this->__('Last %s retailer\'s session was deactivated', $retailer->getName()));
                $retailer->failActiveSession();
            }
            $this->_redirect('*/*');
        }
    }


    public function newAction()
    {
        $this->_forward('edit');
    }

    public function processpriceAction()
    {
        $retailer = $this->_initRetailer();

        try {
            Mage::getModel('magedoc/retailer_data_import')
                ->setRetailer($retailer)
                ->processAll();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Price processed successfully'));
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            if ($retailer->getActiveSession()){
                foreach ($retailer->getActiveSession()->getErrorMessages() as $message){
                    Mage::getSingleton('adminhtml/session')->addError($message);
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($this->__('Price processing failed: %s', $e->getMessage()));
        }

        $this->_redirect('*/*/edit',
            array(
                'id' => $retailer->getId(),
                '_current' => true
            ));

        return;
    }

    public function saveAction()
    {
        $retailer = $this->_initRetailer();

        /** @var Mage_Adminhtml_Model_Session $sessionSingleton */
        $sessionSingleton = Mage::getSingleton('adminhtml/session');

        /** @var Testimonial_Magedoc_Helper_Data $mageDocHelper */
        $mageDocHelper = Mage::helper('magedoc');
        $data = $this->getRequest()->getPost();
        try {
            if (!$data = $this->getRequest()->getPost()) {
                Mage::throwException($mageDocHelper->__('Unable to find item to save'));
            }
            if (!isset($data['retailer']['discount_table'])){
                $data['retailer']['discount_table'] = array();
            }
            if (!isset($data['retailer']['margin_table'])){
                $data['retailer']['margin_table'] = array();
            }

            $configSupply =  $retailer->getSupplyConfig();
            $configSupply->addData($data['retailer_config_supply'])
                ->setOrderHoursEndFormatted( $data['retailer_config_supply']['order_hours_end_formatted'] );

            if (isset($data['rule'])) {
                $importSettingsRule = $retailer->getImportSettingsRule();
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
                $importSettingsRule->loadPost(array('conditions' => $data['conditions']) );
            }

            $retailer->addData($data['retailer'])->save();


            if (isset($_FILES['retailer']['size']['price']) && $_FILES['retailer']['size']['price'] !== 0) {
                if (!empty($data['retailer']['import_adapter_config'])) {
                    $priceFileFormField = 'retailer[price]';
                    $sourceAdapter = Mage::getModel('core/file_uploader', $priceFileFormField);

                    $source = Mage::getModel('magedoc/retailer_data_import_session_source')
                        ->setConfigId($data['retailer']['import_adapter_config'])
                        ->setSourceAdapter($sourceAdapter)
                        ->uploadSourceFile($_FILES['retailer']['name']['price']);

                    $startNewSession =  !is_null( $retailer->getPrepareBaseTable() );
                    if( $startNewSession ) {
                        $retailer->cancelActiveSession();
                    }

                    $retailer->getActiveSession()
                        ->addSource( $source )
                        ->save();
                } else {
                    $sessionSingleton->addError( $mageDocHelper->__('Wrong retailer import config selected') );
                }
            }

            $sessionSingleton->addSuccess(
                $mageDocHelper->__('Retailer was successfully saved')
            );

            $sessionSingleton->setRetailerData(false);
        } catch (Mage_Core_Exception $e) {
            $sessionSingleton->addError($e->getMessage());
            $sessionSingleton->setRetailerData($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $sessionSingleton->addError($this->__('Unable to save retailer: %s', $e->getMessage()));
            $sessionSingleton->setRetailerData($data);
        }

        if ($this->getRequest()->getParam('back')) {
            $this->_redirect('*/*/edit', array(
                'id' => $retailer->getId(),
                '_current' => true
            ));
            return;
        }

        $this->_redirect('*/*/');
    }

    public function insertintopriceAction()
    {
        try {
            $retailer = $this->_initRetailer();

            /** @var Testimonial_MageDoc_Model_Retailer_Data_Import $import */
            $import = Mage::getModel('magedoc/retailer_data_import')
                ->setRetailer($retailer)
                ->importPrice();

            $retailer->completeActiveSession();
            Mage::dispatchEvent('magedoc_import_price', array('retailer' => $retailer, 'import' => $import));
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Price table was updated.'));
        } catch (Exception $e) {
            /*Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::logException($e);*/
            foreach ($retailer->getActiveSession()->getErrorMessages() as $message){
                Mage::getSingleton('adminhtml/session')->addError($message);
            }
        }

        $this->_redirect('*/*/edit', array('_current' => true));
    }

    public function tmppreviewAction()
    {
        $this->_initRetailer();
        $this->loadLayout();

        $this->_prepareDirectorySwitcher('tmppreview_tab_directory_switcher');

        $this->renderLayout();
    }

    public function suppliermappreviewAction()
    {
        $this->_initRetailer();
        $this->loadLayout();

        $this->_prepareDirectorySwitcher('supplier_map_tab_directory_switcher');

        $this->renderLayout();
    }

    protected function _prepareDirectorySwitcher( $switcherBlock )
    {
        if($this->getRequest()->getParam('remove_switcher')) {
            $this->getLayout()->getBlock('root')->unsetChild($switcherBlock);
        } else {
            $this->getLayout()->getBlock($switcherBlock)
                ->setSwitchUrl('magedoc/adminhtml_retailer/edit/' . $this->getRequest()->getParam('id'))
                ->setIsIntoTab(true);
        }
    }


    public function importbrandsAction()
    {
        $retailer = $this->_initRetailer();
        try{
            /** @var Testimonial_MageDoc_Model_Retailer_Data_Import $import */
            $import = Mage::getModel('magedoc/retailer_data_import')
                ->setRetailer($retailer);

            $importedBrands = $import->importBrands();

            $message = sprintf(
                Mage::helper('magedoc')->__('%d suppliers are added to supplier map'),
                $importedBrands
            );
            Mage::getSingleton('adminhtml/session')->addSuccess( $message );

            $this->_redirect( '*/*/processprice', array('_current' => true) );
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError( $e->getMessage() );
            Mage::logException($e);
        }

        $this->_redirect('*/*/edit', array('_current' => true));
    }

    public function importsessionsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function updatepreviewAction()
    {
        $retailer = $this->_initRetailer();
        try {
            Mage::getModel('magedoc/retailer_data_import')
                ->setRetailer($retailer)
                ->updatePreview();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Price preview was updated successfully'));

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::logException($e);
        }

        $this->_redirect('*/*/edit', array('_current' => true));
        return;

    }

    public function deleteAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = Mage::getSingleton('magedoc/retailer')
            ->load($id);
        if (!$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('Retailer no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Retailer has been deleted'));
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('An error occurred while deleting this retailer.'));
        }

        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('magedoc/retailer_data_import_settings_rule'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'importsessions':
                $aclResource = 'magedoc/price/importsessions';
                break;
            default:
                $aclResource = 'magedoc/retailers';
                break;

        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }
}
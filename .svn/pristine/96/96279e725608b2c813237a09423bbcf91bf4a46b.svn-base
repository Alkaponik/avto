<?php
class Testimonial_MageDoc_Model_Retailer_Data_Import extends Mage_Core_Model_Abstract
{
    const DEFAULT_PARSER_MODEL_NAME = 'magedoc/retailer_data_import_parser_base';
    const UPLOAD_IMPORT_PRICE_LOG_FILE_NAME = 'magedoc_upload_import_price.log';
    const RETAILER_IMPORT_LOCK_FILE_NAME = 'retailer_import';

    const XML_PATH_EMAIL_VALIDATE_FAILED_TEMPLATE     = 'magedoc/validate_email/email_template';
    const XML_PATH_EMAIL_SENDER     = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_VALIDATE_FAILED_RECIPIENT_IDENTITY     = 'magedoc/import/administrative_contact_identity';

    protected $_uniqueRowsIndexKeys = array();
    protected $_sourceFile = null;

    protected $_parser = NULL;
    protected $_session = NULL;
    protected $_lastSession = NULL;
    protected $_retailer;

    protected function _construct()
    {
        $this->_init('magedoc/retailer_data_import');
    }

    /**
     * @return Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import
     */
    public function getResource()
    {
        return parent::getResource()
            ->setRetailer( $this->getRetailer() );
    }

    public function setSession( $session )
    {
        $this->_session = $session;
        return $this;
    }

    /**
     * @return Testimonial_MageDoc_Model_Retailer_Data_Import_Session
     */
    public function getSession()
    {
        if( is_null($this->_session) ) {
            $this->_session = $this->getRetailer()->getActiveSession();
        }
        return $this->_session;
    }

    /**
     * @return Testimonial_MageDoc_Model_Retailer_Data_Import_Session
     */
    public function getLastSession()
    {
        if( is_null($this->_lastSession) ) {
            $this->_lastSession = $this->getRetailer()->getLastSession();
        }
        return $this->_lastSession;
    }

    /**
     * @return Testimonial_MageDoc_Model_Retailer
     */
    public function getRetailer()
    {
        if (!isset($this->_retailer)){
            Mage::throwException('No retailer specified');
        }
        return $this->_retailer;
    }

    public function setRetailer($retailer)
    {
        $this->_retailer = $retailer;
        return $this;
    }

    /**
     * @return Testimonial_MageDoc_Model_Retailer_Data_Import_Parser_Abstract
     */
    protected function _initParser( $source = null )
    {
        if(is_null($source) || !$source->getConfig()->getParserModelName()) {

            $parserModelName = static::DEFAULT_PARSER_MODEL_NAME;

            $source = Mage::getModel('magedoc/retailer_data_import_session_source')
                ->setSession( $this->getSession() );
        } else {
            $parserModelName = $source->getConfig()->getParserModelName();
        }

        $this->_parser = Mage::getModel( $parserModelName, array('source' => $source) );

        return $this->_parser;
    }

    public function processAll()
    {
        /** @var MageDoc_System_Helper_Lock $lock */
        $lock = Mage::helper('magedoc_system/lock');

        if (!$lock->isLocked(self::RETAILER_IMPORT_LOCK_FILE_NAME)) {
            $lock->lock(self::RETAILER_IMPORT_LOCK_FILE_NAME);
            try{
                $this->getResource()->getReadConnection()->query('SET SESSION wait_timeout = 600;');
                $this->_initParser()
                    ->prepareTables();
                $this->getSession()->prepare();

                $sources = $this->getSession()->getSources();
                foreach($sources as $source) {
                    $this->processSource($source);
                }
                $lock->unlock(self::RETAILER_IMPORT_LOCK_FILE_NAME);
            }catch(Exception $e){
                $activeSession = $this->getSession();
                if ($e instanceof Mage_Core_Exception) {
                    $message = $e->getMessage();
                } elseif ($e instanceof Exception) {
                    $message = Mage::helper('magedoc')->__('Something went wrong while "process price" running');
                    Mage::logException($e);
                }

                $activeSession->addError(null, null, null, array($message))->save();

                $lock->unlock(self::RETAILER_IMPORT_LOCK_FILE_NAME);
                throw $e;
            }
        } else {
            $message = Mage::helper('magedoc')->__('Unable to process price: process is already running');
            $this->getSession()->addError(null, null, null, array($message))->save();
            Mage::throwException($message);
        }

        return $this;
    }

    public function processSource( $source )
    {
        $parser = $this->_initParser( $source );
        /** @var Testimonial_MageDoc_Model_Retailer_Data_Import_Session $importSession*/
        $parser->insertImportRetailerDataPreview();
        $parser->linkOffersToDirectory();
        $directory = $parser->getDirectoryCode();
        $importSession = $source->getSession();
        $importSession->addData(
            array(
                'total_records' => ($importSession->getTotalRecords() ? : 0) + $importSession->getLastPriceSheetTotalRecords(),
                'valid_records' => ($importSession->getValidRecords() ? : 0) + $importSession->getLastPriceSheetValidRecords(),
                'records_with_old_brands' => $importSession->getRecordsWithOldBrands() + $importSession->getLastPriceSheetRecordsWithOldBrands() ,
                'old_brands'      => $importSession->getOldBrands() + $this->getLinkedSuppliersCount($directory ),
                'total_brands'    => $this->getRetailerTotalBrandsCount(),
                'new_brands'      => $importSession->getNewBrands() + $this->getNotLinkedSuppliersCount($directory),
                'records_linked_to_directory' => $this->getRecordsLinkedToDirectoryCount(),
            )
        )
        ->save();
    }

    public function updatePreview( $supMapIds = null )
    {
        $sources = $this->getSession()->getSources();
        $importSession = $this->getRetailer()->getActiveSession();
        $importSession->addData(
            array(
                 'records_with_old_brands' => 0,
                 'old_brands'      => 0,
                 'new_brands'      => 0,
                 'records_linked_to_directory' => $this->getRecordsLinkedToDirectoryCount( ),
            )
        );
        foreach($sources as $source) {
            $parser = $this->_initParser( $source );
            $parser->updatePreview($supMapIds);
            $directory = $parser->getDirectoryCode();
            $importSession->addData(
                array(
                     'records_with_old_brands' =>
                     $this->getPriceRecordsLinkedToSupplier(),
                     'old_brands'      => $importSession->getOldBrands() + $this->getLinkedSuppliersCount( $directory ),
                     'new_brands'      => $importSession->getNewBrands() + $this->getNotLinkedSuppliersCount( $directory ),
                     'records_linked_to_directory' => $this->getRecordsLinkedToDirectoryCount( ),
                )
            );
        }

        return $this;
    }

    public function getFilePreviewCollection( $previewRowsCount, $useLastSession = false )
    {
        if ($useLastSession && !$this->getRetailer()->hasActiveSession()){
            $session = $this->getRetailer()->getLastFailedSession();
        } else {
            $session = $this->getSession();
        }
        $source = $session->getLastSource();
        return $this->_initParser( $source )->getFilePreviewCollection($previewRowsCount);
    }

    public function importPrice()
    {
        /** @var MageDoc_System_Helper_Lock $lock */
        $lock = Mage::helper('magedoc_system/lock');

        if (!$lock->isLocked(self::RETAILER_IMPORT_LOCK_FILE_NAME)) {
            $lock->lock(self::RETAILER_IMPORT_LOCK_FILE_NAME);
            try{
                if($this->getRetailer()->getImportSettingsRule()->validate($this->getSession())){
                    $this->getResource()->importPrice();
                }else{
                    $this->_sendValidationFailedEmail();
                    Mage::throwException('Price validation failed');
                }
            }catch(Exception $e){
                $activeSession = $this->getSession();
                if ($e instanceof Mage_Core_Exception) {
                    $message = $e->getMessage();
                } elseif ($e instanceof Exception) {
                    $message = Mage::helper('magedoc')->__('Something went wrong while "import price" running');
                    Mage::logException($e);
                }

                $activeSession->addError(null, null, null, array($message))->save();

                $lock->unlock(self::RETAILER_IMPORT_LOCK_FILE_NAME);
                throw $e;
            }
            $lock->unlock(self::RETAILER_IMPORT_LOCK_FILE_NAME);
        } else {
            $message = Mage::helper('magedoc')->__('Unable to import price: import is already running');
            $this->getSession()->addError(null, null, null, array($message))->save();
            Mage::throwException($message);
        }
        return $this;
    }

    public function importBrands( )
    {
        $importedBrands = 0;
        $sources = $this->getRetailer()->getActiveSession()->getSources();
        foreach($sources as $source) {
            $importedBrands += $this->_initParser($source)->importBrands();
        }

        /** @var Testimonial_MageDoc_Model_Retailer_Data_Import_Session $importSession */
        $importSession = Mage::registry('retailer')->getActiveSession();
        $importSession->setImportedBrands($importSession->getImportedBrands() + $importedBrands);
        $importSession->save();

        return $importedBrands;
    }

    public function getDirectoryCode($source)
    {
        return $this->_initParser($source)->getDirectoryCode();
    }

    public function getLinkedSuppliersCount( $directory )
    {
        return $this->getResource()->getLinkedSuppliersCount($directory);
    }

    public function getRetailerTotalBrandsCount()
    {
        return $this->getResource()->getRetailerTotalBrandsCount();
    }

    public function getNotLinkedSuppliersCount( $directory )
    {
        return $this->getResource()->getNotLinkedSuppliersCount($directory);
    }

    public function getRecordsLinkedToDirectoryCount()
    {
        return $this->getResource()->getRecordsLinkedToDirectoryCount();
    }

    public function getPriceRecordsLinkedToSupplier()
    {
        return $this->getResource()->getPriceRecordsLinkedToSupplier();
    }

    //for MageDoc Scheduler

    public function executeImportSchedule($parameters)
    {
        $hlp = Mage::helper('magedoc');

        /** @var MageDoc_System_Helper_Lock $lock */
        $lock = Mage::helper('magedoc_system/lock');
        $lockFileName = 'retailer_import_schedule';
        if(!$lock->isLocked($lockFileName)){
            try {
                $lock->lock($lockFileName);
                $sourceConfig = Mage::getModel('magedoc/retailer_data_import_source_config')
                    ->load($parameters["source_id"]);

                if (!$sourceConfig->getId()) {
                    Mage::throwException("Source config with id = {$sourceConfig->getId()} does not exist");
                }

                $retailer = $sourceConfig->getRetailer();

                $this->_log($hlp->__('Start price upload. Source type: %s. Retailer: %s.',
                    $sourceConfig->getSourceType(), $retailer->getName()));

                $config = new Varien_Object(array(
                    'adapter_ids' => $parameters["adapter_ids"],
                    'start_new_session' => $parameters['start_new_session'],
                    'import_brands' => $parameters['import_brands']
                ));
                if ($sourceConfig->getSourceType() == 'email') {
                    $this->uploadAndImport($sourceConfig, $config);
                }
                $lock->unlock($lockFileName);
            /*} catch( Testimonial_MageDoc_Model_Retailer_Data_Import_Session_Exception $e){
                    $this->getSession()->addError(null, null, null, array($e->getMessage()))->save();
                    $lock->unlock($lockFileName);
                    throw $e;*/
            } catch (Mage_Core_Exception $e) {
                $this->_log($hlp->__('Price import failed: %s', $e->getMessage()));
                $lock->unlock($lockFileName);
                if (isset($retailer) && $retailer->hasActiveSession()) {
                    $retailer->failActiveSession();
                }
                throw $e;
            } catch (Exception $e) {
                $this->_log($hlp->__('Something went wrong while price import running'));
                $lock->unlock($lockFileName);
                if (isset($retailer) && $retailer->hasActiveSession()) {
                    $retailer->failActiveSession();
                }
                throw $e;
            }
        }else{
            Mage::throwException($hlp->__('Unable to upload retailer price: price import is already running'));
        }
    }

    public function uploadAndImport($sourceConfig, $config)
    {
        $hlp = Mage::helper('magedoc');

        /** @var Testimonial_MageDoc_Model_Retailer $retailer */
        $retailer = $sourceConfig->getRetailer();
        $this->setRetailer($retailer);

        if ($config->getStartNewSession()) {
            $retailer->cancelActiveSession();
            $this->_session = null;
        }

        /* @var Testimonial_MageDoc_Model_Import_Source_Adapter */
        $sourceAdapter = Mage::getModel("magedoc/import_source_adapter", $sourceConfig);

        $this->_log($hlp->__('Price downloaded. Source type: %s. Retailer: %s. File name: %s.',
            $sourceConfig->getSourceType(), $retailer->getName(), $sourceAdapter->getFileName()));

        foreach ($config->getAdapterIds() as $adapterId) {
            $source = Mage::getModel('magedoc/retailer_data_import_session_source')
                ->setConfigId($adapterId)
                ->setSourceAdapter($sourceAdapter)
                ->uploadSourceFile($sourceAdapter->getFileName());

            $retailer->getActiveSession()
                ->addSource($source);
        }

        $retailer->getActiveSession()->save();

        $this->processAll();

        if ($config->getImportBrands()) {
            $this->importBrands();
        }

        $this->importPrice();

        Mage::dispatchEvent('magedoc_import_price', array('retailer' => $retailer, 'import' => $this));

        $retailer->completeActiveSession();

        $this->_log($hlp->__('Price upload finished. Source type: %s. Retailer: %s. File name: %s.',
            $sourceConfig->getSourceType(), $retailer->getName(), $sourceAdapter->getFileName()));
    }

    protected function _log($message)
    {
        Mage::log($message, null, self::UPLOAD_IMPORT_PRICE_LOG_FILE_NAME);
    }

    protected function _sendValidationFailedEmail()
    {
        $emailTemplate = Mage::getModel('core/email_template');
        /* @var $emailTemplate Mage_Core_Model_Email_Template */
        $emailTemplate->setTemplateSubject(Mage::helper('magedoc')->__('Price validate failed'));

        $storeId = Mage::app()->getStore()->getId();

        $recipient = Mage::getStoreConfig(self::XML_PATH_EMAIL_VALIDATE_FAILED_RECIPIENT_IDENTITY);

        $message = new Varien_Object(
            array('retailer_name' => $this->getRetailer()->getName(),
                'file_name' => basename($this->getSession()->getLastSource()->getSourcePath()),
                'error' => implode(', ', $this->getSession()->getErrorMessages()))
        );

        $emailTemplate->setDesignConfig(array('area' => 'backend'))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_EMAIL_VALIDATE_FAILED_TEMPLATE),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                Mage::getStoreConfig("trans_email/ident_$recipient/email", $storeId),
                null,
                array('message' => $message)
            );

        return $this;
    }
}
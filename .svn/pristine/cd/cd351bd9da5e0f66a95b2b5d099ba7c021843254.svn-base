<?php

class Testimonial_MageDoc_Model_Retailer extends Mage_Core_Model_Abstract 
{
    const RETAILER_UPDATE_MODELS_PATH = 'global/retailer_update_models';
    const RETAILER_UPDATE_MODEL_DEFAULT = 'default';
    const ACTUAL_PRICE_TERM = 168;
    const PRICE_NEVER_IMPORTED_LAST_IMPORT_DATE = '0000-00-00 00:00:00';
    const STOCK_STATUS_IN_STOCK = 1;

    protected $_config;
    protected $_supplyConfig;
    protected $_importSettingsRule;
    protected $_priceCrontab = null;
    protected $_sessionData;
    protected $_importConfigs = null;
    protected $_importSources = null;
    protected $_lastSession = false;
    protected $_previousSession = false;
    protected $_activeSession = null;
    protected $_eventPrefix = 'magedoc_retailer';

    protected function _construct()
    {
        $this->_init('magedoc/retailer');
    }   
    
    public function getConfig()
    {
        if(!isset($this->_config)){
            $this->_config = Mage::getModel('magedoc/retailer_config')->load($this->getId());
            if($this->_config->getId()){
                $this->_config->setRetailer($this);
            }
        }
        return $this->_config;
    }

    public function getSupplyConfig()
    {
        if(!isset($this->_supplyConfig)){
            $this->_supplyConfig = Mage::getModel('magedoc/retailer_config_supply')->load($this->getId());
            $this->_supplyConfig->setRetailer($this);
        }
        return $this->_supplyConfig;
    }

    public function getImportSettingsRule()
    {
        if(!isset($this->_importSettingsRule)){
            $this->_importSettingsRule = Mage::getModel('magedoc/retailer_data_import_settings_rule')
                ->load($this->getId(),'retailer_id');
            $this->_importSettingsRule->setRetailer($this);
        }
        return $this->_importSettingsRule;
    }


    public function getUpdateModel()
    {
        if(!isset($this->_updateModel)) {
            if($name = $this->getData('update_model')){
                $model = (string) Mage::getConfig()
                        ->getNode(self::RETAILER_UPDATE_MODELS_PATH . '/' 
                            . $name . "/class");                
            }else{
                $model = (string) Mage::getConfig()
                        ->getNode(self::RETAILER_UPDATE_MODELS_PATH . '/' 
                            . self::RETAILER_UPDATE_MODEL_DEFAULT . "/class");                
            }
            $this->_updateModel = Mage::getModel($model);
            $this->_updateModel->setRetailer($this);      
        }
        return $this->_updateModel;
    }

   
    protected function _insertData($productsData)
    {
        $importTable = Mage::getResourceModel('magedoc/import_retailer_data')->getMainTable();
        if ($productsData) {
            Mage::getSingleton('core/resource')->getConnection('write')->insertOnDuplicate(
                $importTable,
                $productsData,
                array(
                    'cost',
                    'price',
                    'manufacturer',
                    'domestic_stock_qty',
                    'general_stock_qty',
                    'qty',
                    'updated_at'
                ));
        }
        return $this;
    }

    /**
     * Get the session data
     * @return Varien_Object
     */
    public function getSessionData() {
        if(!isset($this->_sessionData)){
            $data = array();
            $value = $this->getData('session_data');
            $unserializedData = (empty($value) ? false : unserialize($value));
            if (is_array($unserializedData)){
                $data = $unserializedData;
            }
            $this->_sessionData = new Varien_Object($data);
        }
        return $this->_sessionData;
    }

    protected function _beforeSave()
    {
        $this->setSessionData(serialize($this->getSessionData()->getData()));
        parent::_beforeSave();
    }

    protected function _afterSave() {
        parent::_afterSave();
        foreach( $this->getImportSourceCollection() as $sourceItem) {
            $sourceItem->setRetailerId($this->getId())->save();
        }
        foreach( $this->getImportConfigCollection() as $configItem) {
            $configItem->setRetailerId($this->getId())->save();
        }
        foreach( $this->getPriceCrontabCollection() as $crontabItem) {
            $crontabItem->setRetailerId($this->getId())->save();
        }
        $this->getSupplyConfig()->setRetailerId($this->getId())->save();
        $this->getImportSettingsRule()->setRetailerId($this->getId())->save();
    }

    public function createImportRetailerDataTable( $tmp = true )
    {
        $this->_getResource()->createImportRetailerDataTable($tmp);
    }

    /**
     * @return Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Adapter_Config_Collection
     */

    public function getImportConfigCollection()
    {
        if (is_null($this->_importConfigs)) {
            $this->_importConfigs = Mage::getResourceModel('magedoc/retailer_data_import_adapter_config_collection')
                ->setRetailer($this);
        }
        return $this->_importConfigs;
    }

    public function addImportConfig(Varien_Object $config)
    {
        $config->setRetailer($this);
        $this->getImportConfigCollection()->addItem($config);

        return $config;
    }
    /**
     * @return Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Source_Config_Collection
     */

    public function getImportSourceCollection()
    {
        if (is_null($this->_importSources)) {
            $this->_importSources = Mage::getResourceModel('magedoc/retailer_data_import_source_config_collection')
                ->setRetailer($this);
        }
        return $this->_importSources;
    }

    public function addImportSource(Varien_Object $source)
    {
        $source->setRetailer($this);
        $this->getImportSourceCollection()->addItem($source);

        return $source;
    }

    public function getPriceCrontabCollection()
    {
        if (is_null($this->_priceCrontab)) {
            /*$this->_priceCrontab = Mage::getResourceModel('magedoc/retailer_data_price_upload_crontab_collection')
                ->setRetailer($this);*/
            $this->_priceCrontab = Mage::getResourceModel('magedoc_scheduler/crontab_collection')
                ->setReference($this)->load();
        }
        return $this->_priceCrontab;
    }

    public function addPriceCrontab(Varien_Object $crontab)
    {
        $crontab->setReference($this);
        $this->getPriceCrontabCollection()->addItem($crontab);

        return $crontab;
    }


    /**
     * @return Testimonial_MageDoc_Model_Retailer_Data_Import_Session
     */
    public function getActiveSession()
    {
        if(is_null($this->_activeSession)) {
            if ( !$this->_activeSession = $this->_getActiveSession() ) {
                $this->_activeSession =
                    Mage::getModel('magedoc/retailer_data_import_session')
                        ->setData(
                            array(
                                'config_id'   => $this->getImportAdapterConfig(),
                                'status_id'   => Testimonial_MageDoc_Model_Retailer_Data_Import_Session::
                                    SESSION_STATUS_PENDING
                            )
                        );
                $this->_activeSession->setRetailer($this);
                $this->_lastSession = $this->_activeSession;
            }

            if(!$this->getImportAdapterConfigModel()) {
                $importAdapterConfigId = $this->_activeSession->getConfigId();

                $this->setImportAdapterConfigModel(
                    Mage::getModel('magedoc/retailer_data_import_adapter_config')->load($importAdapterConfigId)
                );

            }
        }

        return $this->_activeSession;
    }

    public function cancelActiveSession()
    {
        if( $activeSession = $this->_getActiveSession() ) {
            $activeSession->cancel();

            $this->_activeSession = null;
        }

        return $this;
    }

    public function failActiveSession()
    {
        if( $activeSession = $this->_getActiveSession() ) {
            $activeSession->fail();

            $this->_activeSession = null;
        }

        return $this;
    }

    protected function _getActiveSession()
    {
        if (($session = $this->getLastSession())
            && $session->isActive()) {
            return $session;
        }
        return false;
    }

    /**
     * @return bool|Testimonial_MageDoc_Model_Retailer_Data_Import_Session
     */

    public function getLastSession()
    {
        if ($this->_lastSession === false){
            $sessionCollection =  $this->getSessionCollection()
                ->setPageSize(1)
                ->setOrder('session_id')
                ->load();
            if ($sessionCollection->count()) {
                $session = $sessionCollection->getFirstItem();
                $resource = $session->getResource();
                $resource->unserializeFields($session);
                $session->setRetailer($this);
                $this->_lastSession = $session;
            }
        }

        return $this->_lastSession;
    }

    public function getPreviousSession(
        $status = Testimonial_MageDoc_Model_Retailer_Data_Import_Session::SESSION_STATUS_COMPLETE)
    {
        if ($this->_previousSession === false){
            $sessionCollection =  $this->getSessionCollection()
                ->addFieldToFilter('session_id', array("neq"=>$this->getActiveSession()->getId()))
                ->addFieldToFilter('status_id', $status)
                ->setPageSize(1)
                ->setOrder('session_id')
                ->load();
            if ($sessionCollection->count()) {
                $session = $sessionCollection->getFirstItem();
                $resource = $session->getResource();
                $resource->unserializeFields($session);
                $this->_previousSession = $session;
            }
        }

        return $this->_previousSession;
    }

    public function getLastFailedSession()
    {
        $session = $this->getLastSession();
        if ($session &&
            $session->isFailed()){
            return $session;
        }
        return false;
    }

    /**
     * @return Testimonial_MageDoc_Model_Mysql4_Retailer_Data_Import_Session_Collection
     */

    public function getSessionCollection()
    {
        $collection = Mage::getResourceModel('magedoc/retailer_data_import_session_collection')
            ->setRetailer($this);
        return $collection;
    }

    public function completeActiveSession()
    {
        if( $activeSession = $this->_getActiveSession() ) {
            $activeSession->complete();
        }
        $this->_activeSession = null;
    }

    public function hasActiveSession()
    {
        if($this->_getActiveSession() || (!is_null($this->_activeSession))) {
            return true;
        }

        return false;
    }

    public function isPriceUpdateValid()
    {
        $priceValidityTerm = $this->getPriceValidityTerm();
        return $this->_isPassedSinceLastUpdate($priceValidityTerm);
    }

    public function isPriceExpiresSoon()
    {
        $priceValidityTerm = $this->getPriceValidityTerm();
        $priceValidityNotificationTerm = Mage::getStoreConfig('magedoc/import/price_validity_notification_term');
        if( $priceValidityTerm < $priceValidityNotificationTerm ) {
            $priceValidityNotificationTerm = 0;
        }
        return $this->_isPassedSinceLastUpdate( $priceValidityTerm - $priceValidityNotificationTerm);
    }

    protected function _isPassedSinceLastUpdate( $hours )
    {
        $importDate = $this->getLastImportDate();
        $importDate = new Zend_Date( $importDate, Zend_Date::ISO_8601 );
        $importDate->addHour( $hours );

        return ( $importDate->compare( time() ) < 0 );
    }

    public function getPriceValidityTerm()
    {
        $priceValidityTerm = ( isset($this->_data['price_validity_term']) && $this->_data['price_validity_term'] > 0 ) ?
            $this->_data['price_validity_term'] : Mage::getStoreConfig('magedoc/import/valid_price_term');

        if(!$priceValidityTerm) {
            $priceValidityTerm = static::ACTUAL_PRICE_TERM;
        }

        return $priceValidityTerm;
    }

    public function isEverPriceImported()
    {
        return $this->getLastImportDate() !== self::PRICE_NEVER_IMPORTED_LAST_IMPORT_DATE;
    }

    public function getDiscountTable($customerGroupId = Testimonial_MageDoc_Helper_Price::DEFAULT_CUSTOMER_GROUP_ID)
    {
        if ($this->getData('discount_table')){
            return Mage::helper('magedoc/price')->arrayToHash($this->getData('discount_table'));
        }
        return Mage::helper('magedoc/price')->getDiscountTable($customerGroupId);
    }

    public function getMarginTable($customerGroupId = Testimonial_MageDoc_Helper_Price::DEFAULT_CUSTOMER_GROUP_ID)
    {
        if ($this->getData('margin_table')){
            return Mage::helper('magedoc/price')->arrayToHash($this->getData('margin_table'));
        }
        return Mage::helper('magedoc/price')->getMarginTable($customerGroupId);
    }

    public function getPriceWithDiscount($price, $customerGroupId = Testimonial_MageDoc_Helper_Price::DEFAULT_CUSTOMER_GROUP_ID)
    {
        $marginRatio = $this->getMarginRatio();
        $discountTable = $this->getDiscountTable($customerGroupId);
        return Mage::helper('magedoc/price')->getPriceWithDiscount($price, $marginRatio, $discountTable);
    }

    public function getPriceWithMargin($cost, $customerGroupId = Testimonial_MageDoc_Helper_Price::DEFAULT_CUSTOMER_GROUP_ID)
    {
        $marginRatio = $this->getMarginRatio();
        $marginTable = $this->getMarginTable($customerGroupId);
        return Mage::helper('magedoc/price')->getPriceWithMargin($cost, $marginRatio, $marginTable);
    }
}

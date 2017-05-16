<?php
class Ak_NovaPoshta_Model_Import
{
    const UA_COUNTRY_CODE = 'UA';
    const BACK_DELIVERY = 1;


    /** @var int Number of objects to save in one mysql statement when saving cities */
    protected $_bulkSizeCity = 50;

    /**
     * with higher value I got segfault error (see http://framework.zend.com/issues/browse/ZF-11249)
     * @var int Number of objects to save in one mysql statement when saving warehouses
     */
    protected $_bulkSizeWarehouse = 20;

    protected $_exisitngCities;

    protected $_exisitngWarehouses;

    public $numConsignmentsUpdate = 0;

    protected $_dataMapCity = array(
        'id'          => 'id',
        'nameRu'      => 'name_ru',
        'nameUkr'     => 'name_ua',
        'areaNameUkr' => 'area_name_ua'
    );

    protected $_dataMapFormPayment = array(
        '1' => 2,
        '2' => 1
    );
    protected $_dataMapPayer = array(
        '1' => 0,
        '2' => 1
    );

    protected $_dataMapWarehouse = array(
        'wareId'                   => 'id',
        'city_id'                  => 'city_id',
        'address'                  => 'address_ua',
        'addressRu'                => 'address_ru',
        'phone'                    => 'phone',
        'weekday_work_hours'       => 'weekday_work_hours',
        'weekday_reseiving_hours'  => 'weekday_reseiving_hours',
        'weekday_delivery_hours'   => 'weekday_delivery_hours',
        'saturday_work_hours'      => 'saturday_work_hours',
        'saturday_reseiving_hours' => 'saturday_reseiving_hours',
        'saturday_delivery_hours'  => 'saturday_delivery_hours',
        'max_weight_allowed'       => 'max_weight_allowed',
        'x'                        => 'longitude',
        'y'                        => 'latitude',
        'number'                   => 'number_in_city'
    );

    protected $_dataMapConsignment = array(
        'originalTTN'          => 'ttn',
        'status'               => 'status',
        'stage'                => 'stage',
        'CitySenderRU'         => 'city_sender_ru',
        'CitySenderUA'         => 'city_sender_ua',
        'DateEstimated'        => 'date_estimated',
        'Payer'                => 'payer',
        'FromPayment'          => 'form_payment',
        'Sum'                  => 'sum',
        'DeliveryForm'         => 'delivery_form',
        'WareReceiverUA'       => 'ware_receiver_ua',
        'WareReceiverRU'       => 'ware_receiver_ru',
        'BackDelivery'         => 'back_delivery',
        'CityReceiverUA'       => 'city_receiver_ua',
        'CityReceiverRU'       => 'city_receiver_ru',
        'DateReceived'         => 'date_received',
        'Receiver'             => 'receiver',
        'Redelivery'           => 'redelivery',
        'redeliveryPrice'      => 'redelivery_price',
        'redeliverySum'        => 'redelivery_sum',
        'fullDescription'      => 'full_description',
        'additionalInfo'       => 'additional_info',
        'parentDocumentNumber' => 'parent_document_number'
    );

    protected $_dataMapDocumentTracking = array(
        'Barcode'              => 'ttn',
        'StateId'              => 'state',
        'StateName'            => '',
        'CheckWeight'          => '',
        'DocumentCost'         => '',
        'DateReceived'         => 'date_received',
        'RecipientFullName'    => 'receiver',
        'RecipientPost'        => '',
        'ReceiptDateTime'      => '',
        'OnlinePayment'        => '',
        'DeliveryForm'         => 'delivery_form',
        'AddressUA'       => 'ware_receiver_ua',
        'AddressRU'       => 'ware_receiver_ru',
        'WareReceiverId'       => '',
        'BackDelivery'         => 'back_delivery',
        'RedeliveryNUM'        => 'redelivery',
        'CityReceiverSiteKey'  => '',
        'CityReceiverUA'       => 'city_receiver_ua',
        'CityReceiverRU'       => 'city_receiver_ru',
        'CitySenderSiteKey'    => '',
        'CitySenderRU'         => 'city_sender_ru',
        'CitySenderUA'         => 'city_sender_ua',
        'DeliveryType'         => '',
        'BackwardDeliveryNumber' => '',
        'RedeliveryCargoDescriptionMoney' =>'',
        'Failure'              => '',
        'ReasonDescription'    => '',
        'GlobalMoneyExistDelivery' => '',
        'GlobalMoneyLastTransactionStatus' =>'',
        'GlobalMoneyLastTransactionDate' => '',
        'Sum'                  => 'sum',
        'DocumentWeight'       => '',
        'SumBeforeCheckWeight' => '',
        'isEWPaid'             => '',
        'isEWPaidCashLess'     => '',
        'ewPaidSumm'           => '',
        'RedeliverySum'        => 'redelivery_sum',
        'OwnerDocumentType'    => '',
        'ChildDocuments'       => '@child_documents',
        
        /* Old unused fields */
        'DateEstimated'        => 'date_estimated',
        'Payer'                => 'payer',
        'FromPayment'          => 'form_payment',
        'redeliveryPrice'      => 'redelivery_price',
        'fullDescription'      => 'full_description',
        'additionalInfo'       => 'additional_info',
        'parentDocumentNumber' => 'parent_document_number'
    );

    private $_finalStatus = array(
        Ak_NovaPoshta_Model_Source_Tracking_Status::STATUS_INVALID_NUMBER,
        Ak_NovaPoshta_Model_Source_Tracking_Status::STATUS_SHIPMENT_RECEIVED);

    private $_finalStates = array(
        Ak_NovaPoshta_Model_Source_Tracking_State::INVALID_NUMBER,
        Ak_NovaPoshta_Model_Source_Tracking_State::REMOVED,
        Ak_NovaPoshta_Model_Source_Tracking_State::RECEIVED,
        Ak_NovaPoshta_Model_Source_Tracking_State::RETIRED_BY_SENDER,
        );

    private $_consignmentsCollection = array();

    protected $_regionAliases = array(
        'АР Крим'   =>  'АРК'
    );

    protected $_countryRegions = array();

    public function __construct()
    {
        $this->_initCountryRegions();
    }

    protected function _initCountryRegions()
    {
        $origLocaleCode = Mage::app()->getLocale()->getLocaleCode();
        Mage::app()->getLocale()->setLocaleCode('uk_UA');
        $countryId = self::UA_COUNTRY_CODE;
        $countryNormalized = strtolower($countryId);
        $this->_countryRegions[$countryNormalized] = array();
        $regions = Mage::getResourceModel('directory/region_collection')
                    ->addCountryFilter($countryId);
        foreach ($regions as $regionRow) {
            $regionName = mb_strtolower($regionRow['name'], 'UTF-8');
            $this->_countryRegions[$countryNormalized][$regionName] = $regionRow['region_id'];
        }
        Mage::app()->getLocale()->setLocaleCode($origLocaleCode);

        foreach ($this->_regionAliases as $regionName => $alias){
            $regionName = mb_strtolower($regionName, 'UTF-8');
            $alias = mb_strtolower($alias, 'UTF-8');

            if (isset($this->_countryRegions[$countryNormalized][$regionName])){
                $this->_countryRegions[$countryNormalized][$alias] = $this->_countryRegions[$countryNormalized][$regionName];
            }
        }
    }

    /**
     * @throws Exception
     * @return Ak_NovaPoshta_Model_Import
     */
    public function runWarehouseAndCityMassImport()
    {
        $apiKey = Mage::helper('novaposhta')->getStoreConfig('api_key');
        $apiUrl = Mage::helper('novaposhta')->getStoreConfig('api_url');
        if (!$apiKey || !$apiUrl) {
            Mage::helper('novaposhta')->log('No API key or API URL configured');
            throw new Exception('No API key or API URL configured');
        }

        try {
            /** @var $apiClient Ak_NovaPoshta_Model_Api_Client */
            $apiClient = Mage::getModel('novaposhta/api_client', array($apiUrl, $apiKey));

            Mage::helper('novaposhta')->log('Start city import');
            $cities = $apiClient->getCityWarehouses();
            $this->_importCities($cities);
            Mage::helper('novaposhta')->log('End city import');

            Mage::helper('novaposhta')->log('Start warehouse import');
            $warehouses = $apiClient->getWarehouses();
            $this->_importWarehouses($warehouses);
            Mage::helper('novaposhta')->log('End warehouse import');
            Mage::dispatchEvent('novaposhta_warehouse_update_complete', array('cities' => &$cities, 'warehouses' => &$warehouses));
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->__toString());
            throw $e;
        }

        return $this;
    }

    /**
     * @param SimpleXMLElement $cities
     *
     * @throws Exception
     * @return bool
     */
    protected function _importCities(SimpleXMLElement $cities)
    {
        if (empty($cities)) {
            Mage::helper('novaposhta')->log('No city with warehouses received');
            throw new Exception('No city with warehouses received');
        }

        $cities = $this->_applyMap($cities, $this->_dataMapCity, '_applyCityMap');

        if (count($cities) > 0) {
            $cities = array_chunk($cities, $this->_bulkSizeCity);
            foreach ($cities as $chunk) {
                $sql = 'INSERT INTO `novaposhta_city` (' . implode(', ', array_keys($chunk[0])) . ') VALUES ';
                foreach ($chunk as $cityToInsert) {
                    $sql .= '("' . implode('", "', $cityToInsert) . '"), ';
                }
                $sql = trim($sql, ', ');
                $sql .= ' ON DUPLICATE KEY UPDATE ';
                foreach (array_keys($chunk[0]) as $field) {
                    $sql .= "$field = VALUES($field), ";
                }
                $sql = trim($sql, ', ');
                $this->_getConnection()->query($sql);
            }
        }

        return true;
    }

    /**
     * @return array
     */
    protected function &_getExistingCities()
    {
        if (!$this->_exisitngCities) {
            $existingCitiesTemp = Mage::getResourceModel('novaposhta/city_collection')->getSelect();
            $existingCitiesTemp = $this->_getConnection()->query($existingCitiesTemp)->fetchAll();
            if (empty($existingCitiesTemp)) {
                $existingCitiesTemp = array();
            }

            $this->_exisitngCities = array();
            foreach ($existingCitiesTemp as $existingCity) {
                $this->_exisitngCities[$existingCity['id']] = $existingCity;
            }

            unset($existingCitiesTemp);
        }
        return $this->_exisitngCities;
    }

    /**
     * @param array $existingCity
     * @param array $city
     *
     * @return bool
     */
    protected function _isCityChanged(array $existingCity, array $city)
    {
        foreach ($existingCity as $key => $value) {
            if (isset($city[$key]) && $city[$key] != $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SimpleXMLElement $apiObjects
     * @param                  $map
     *
     * @return array
     */
    protected function _applyMap(SimpleXMLElement $apiObjects, $map, $callback = null)
    {
        $resultingArray = array();
        $idKey = array_search('id', $map);
        foreach ($apiObjects as $apiObject) {
            $id = (string)$apiObject->$idKey;
            $resultingArray[$id] = array();
            foreach ($apiObject as $apiKey => $value) {
                if (!isset($map[$apiKey])) {
                    continue;
                }
                $resultingArray[$id][$map[$apiKey]] = addcslashes((string)$value, "\000\n\r\\'\"\032");
            }
            if (method_exists($this, $callback)){
                $this->$callback($apiObject, $resultingArray[$id], $map);
            }
        }

        return $resultingArray;
    }

    protected function _applyCityMap($apiObject, &$resultingArray, &$map)
    {
        $regionNormalized = mb_strtolower((string)$apiObject->areaNameUkr, 'UTF-8');
        if (isset($this->_countryRegions[strtolower(self::UA_COUNTRY_CODE)][$regionNormalized])){
            $resultingArray['region_id'] = $this->_countryRegions[strtolower(self::UA_COUNTRY_CODE)][$regionNormalized];
        }else{
            $resultingArray['region_id'] = null;
        }
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * @param SimpleXMLElement $warehouses
     *
     * @throws Exception
     * @return bool
     */
    protected function _importWarehouses(SimpleXMLElement $warehouses)
    {
        if (empty($warehouses)) {
            Mage::helper('novaposhta')->log('No warehouses received');
            throw new Exception('No warehouses received');
        }

        $resource = Mage::getResourceModel('novaposhta/warehouse');
        $warehouses = $this->_applyMap($warehouses, $this->_dataMapWarehouse);
        $existingWarehouses = $this->_getExistingWarehouses();
        $warehousesToDelete = array_diff(array_keys($existingWarehouses), array_keys($warehouses));

        if (count($warehousesToDelete) > 0) {
            $warehousesToDelete = implode(', ', $warehousesToDelete);
            $sql = "DELETE FROM `novaposhta_warehouse` WHERE `id` IN ($warehousesToDelete)";
            $this->_getConnection()->query($sql);
            Mage::helper('novaposhta')->log("Warehouses deleted: $warehousesToDelete");
        }

        if (count($warehouses) > 0) {
            $warehouses = array_chunk($warehouses, $this->_bulkSizeWarehouse);
            foreach ($warehouses as $chunk) {
                $this->_getConnection()->insertOnDuplicate($resource->getMainTable(), $chunk, array_keys($chunk[0]));
            }
        }

        return true;
    }

    /**
     * @return array
     */
    protected function &_getExistingWarehouses()
    {
        if (!$this->_exisitngWarehouses) {
            $existingWarehousesTemp = Mage::getResourceModel('novaposhta/warehouse_collection')->getSelect();
            $existingWarehousesTemp = $this->_getConnection()->query($existingWarehousesTemp)->fetchAll();
            if (empty($existingWarehousesTemp)) {
                $existingWarehousesTemp = array();
            }

            $this->_exisitngWarehouses = array();
            foreach ($existingWarehousesTemp as $existingWarehouse) {
                $this->_exisitngWarehouses[$existingWarehouse['id']] = $existingWarehouse;
            }

            unset($existingWarehousesTemp);
        }
        return $this->_exisitngWarehouses;
    }

    public function runCheckStatusConsignments()
    {
        try {
            $consignmentsCollection = $this->getConsignmentCollection();

            Mage::helper('novaposhta')->log('Start consignment status synchronization.');
            foreach ($consignmentsCollection as $consignment) {
                $this->updateConsignment($consignment);
            }
            Mage::helper('novaposhta')->log('End consignment status synchronization.');

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->__toString());
            throw $e;
        }
    }

    /**
     *
     * @param Ak_NovaPoshta_Model_Consignment $consignment
     * @return Ak_NovaPoshta_Model_Consignment
     */
    public function updateConsignment(Ak_NovaPoshta_Model_Consignment $consignment)
    {
        return $this->documentsTracking($consignment);
    }

    /**
     * @deprecated method (old API support)
     * @param Ak_NovaPoshta_Model_Consignment $consignment
     * @return Ak_NovaPoshta_Model_Consignment
     */
    public function _updateConsignment(Ak_NovaPoshta_Model_Consignment $consignment)
    {
        return $this->documentsTracking($consignment);
        if ($consignment->canUpdate()) {
            /** @var $apiClient Testimonial_NovaPoshta_Model_Api_Client */
            $apiClient = $this->getClientApi();

            if (!is_null($consignment->getTtn())) {
                $consignmentData = $this->normalizeConsignmentData($this->_applyConsignmentMap($apiClient->getConsignmentStatus($consignment->getTtn()), $this->_dataMapConsignment));
                $this->numConsignmentsUpdate++;
                $consignment->addData($consignmentData)
                    ->save();
                if ($consignment->dataHasChangedFor('redelivery')) {
                    $redeliveryTtn = $consignmentData['redelivery'];
                    $redeliveryData = $this->normalizeConsignmentData($this->_applyConsignmentMap($apiClient->getConsignmentStatus($consignmentData['redelivery']), $this->_dataMapConsignment));
                    $this->_saveRelatedDocument($consignment, $redeliveryData, $redeliveryTtn);
                }
            }
        }
        return $consignment;
    }

    public function documentsTracking(Ak_NovaPoshta_Model_Consignment $consignment)
    {
        if ($consignment->canUpdate()) {
            /** @var $apiClient Testimonial_NovaPoshta_Model_Api_Client */
            $apiClient = $this->getClientApi();

            if ($consignment->getTtn()) {
                $consignmentData = $this->normalizeConsignmentData($this->_applyDocumentTrackingMap($apiClient->documentsTracking($consignment->getTtn()), $this->_dataMapDocumentTracking));
                $this->numConsignmentsUpdate++;
                $consignment->addData($consignmentData)
                    ->save();
                if ($consignment->dataHasChangedFor('redelivery')) {
                    $redeliveryData = $this->normalizeConsignmentData($this->_applyDocumentTrackingMap($apiClient->documentsTracking($consignmentData['redelivery']), $this->_dataMapDocumentTracking));
                    $this->_saveRelatedDocument($consignment, $redeliveryData);
                }
                if ($childDocuments = $consignment->getChildDocuments()) {
                    $childDocumentsData = $this->normalizeConsignmentData($this->_applyDocumentTrackingMap($childDocuments, $this->_dataMapDocumentTracking));
                    $this->_saveRelatedDocument($consignment, $childDocumentsData);
                }
            }
        }
        return $consignment;
    }

    protected function _saveRelatedDocument($consignment, $relatedDocumentData, $relatedTtn = null)
    {
        if (is_null($relatedTtn)){
            if (isset($relatedDocumentData['ttn'])) {
                $relatedTtn = $relatedDocumentData['ttn'];
            } else {
                return false;
            }
        }
        $isRedelivery = $consignment->getRedelivery() == $relatedTtn;
        $redelivery = Mage::getModel('novaposhta/consignment')->loadByTtn($relatedTtn);
        $redelivery->addData($relatedDocumentData);
        $redelivery->setOrderId($consignment->getOrderId());
        $redelivery->setOrderIncrementId($consignment->getOrderIncrementId());
        $redelivery->setCustomerId($consignment->getCustomerId());
        $redelivery->setShipmentId($consignment->getShipmentId());
        if ($consignment->getRedeliverySum()
            && $isRedelivery){
            $redelivery->setRedeliverySum($consignment->getRedeliverySum());
            $redelivery->setBackDelivery(0);
        }
        $redelivery->setTtn($relatedTtn);
        $redelivery->setIsBackDelivery(self::BACK_DELIVERY);
        $redelivery->save();
        return $redelivery;
    }
    
    public function normalizeConsignmentData($consignmentData)
    {
        if (isset($consignmentData['form_payment']) && isset($this->_dataMapFormPayment[$consignmentData['form_payment']])){
            $consignmentData['form_payment'] = $this->_dataMapFormPayment[$consignmentData['form_payment']];
        }
        if (isset($consignmentData['payer']) && isset($this->_dataMapPayer[$consignmentData['payer']])){
            $consignmentData['payer'] = $this->_dataMapPayer[$consignmentData['payer']];
        }
        if (empty($consignmentData['redelivery'])){
            unset($consignmentData['redelivery']);
        }
        if (empty($consignmentData['redelivery_sum'])){
            unset($consignmentData['redelivery_sum']);
        }
        return $consignmentData;
    }

    /**
     *
     * @return Testimonial_NovaPoshta_Model_Api_Client
     * @throws Exception
     */
    public function getClientApi()
    {
        $apiKey = Mage::helper('novaposhta')->getStoreConfig('api_key');
        $apiUrl = Mage::helper('novaposhta')->getStoreConfig('api_url');
        if (!$apiKey || !$apiUrl) {
            Mage::helper('novaposhta')->log('No API key or API URL configured');
            throw new Exception('No API key or API URL configured');
        }
        return Mage::getSingleton('novaposhta/api_client', array($apiUrl, $apiKey));
    }

    /**
     *
     * @return Ak_NovaPoshta_Model_Resource_Consignment_Collection
     */
    public function getConsignmentCollection()
    {
        $consignmentsCollection = Mage::getResourceModel('novaposhta/consignment_collection');

        $dateFrom = new DateTime();
        $dateFrom = $dateFrom->sub(new DateInterval('P3M'))->format('Y-m-d H:i:s');
        $consignmentsCollection->addFieldToFilter('status', array(array('nin' => $this->_finalStatus), array('null' => true)))
            ->addFieldToFilter('state', array(array('nin' => $this->_finalStates), array('null' => true)))
            ->addFieldToFilter('ttn', array('notnull' => true))
            ->addFieldToFilter('created_at', array('gteq' => $dateFrom));
        //$consignmentsCollection->printLogQuery(true);
        return $consignmentsCollection;
    }

    /**
     * @param SimpleXMLElement $apiObject
     * @param array $map
     * @return array
     */
    protected function _applyConsignmentMap($apiObject, $map)
    {   
        //echo $apiObject->asXML();
        $resultingArray = array();
        foreach ($apiObject as $apiKey => $value) {
            $value = (string) $value;
            if (!isset($map[$apiKey])/* || empty($value)*/) {
                continue;
            }

            $resultingArray[$map[$apiKey]] = addcslashes($value, "\000\n\r\\'\"\032");
        }
        return $resultingArray;
    }

    /**
     * @param SimpleXMLElement $apiObject
     * @param array $map
     * @return array
     */
    protected function _applyDocumentTrackingMap($apiObject, $map)
    {   
        //echo $apiObject->asXML();
        $resultingArray = array();
        if (!$apiObject
            || ! current($apiObject->xpath('data/item'))){
            return $resultingArray;
        }

        $apiObject = current($apiObject->xpath('data/item'));

        foreach ($apiObject as $apiKey => $value) {
            if (empty($map[$apiKey])/* || empty($value)*/) {
                continue;
            }
            $key = $map[$apiKey];
            if (substr($key, 0, 1) != '@'){
                $value = (string) $value;
                $value = addcslashes($value, "\000\n\r\\'\"\032");
            } else {
                $key = substr($key, 1);
            }

            $resultingArray[$key] = $value;
        }
        return $resultingArray;
    }
}
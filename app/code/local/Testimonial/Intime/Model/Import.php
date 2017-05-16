<?php
class Testimonial_Intime_Model_Import
{
    const UA_COUNTRY_CODE = 'UA';
    const BACK_DELIVERY = 1;

    /** @var int Number of objects to save in one mysql statement when saving cities */
    protected $_bulkSizeCity = 100;

    /**
     * with higher value I got segfault error (see http://framework.zend.com/issues/browse/ZF-11249)
     * @var int Number of objects to save in one mysql statement when saving warehouses
     */
    protected $_bulkSizeWarehouse = 20;

    protected $_exisitngCities;

    protected $_exisitngWarehouses;

    protected $_dataMapCity = array(
        'Name'      => 'name',
        'Code'      => 'code',
        'Phone'     => 'phone',
        'Adress'    => 'adress'
    );

    protected $_dataMapWarehouse = array(
        'Name'   => 'name',
        'Code'   => 'city_code',
        'Phone'  => 'phone',
        'Adress' => 'adress',
        'Nuber'  => 'number'
    );

    protected $_dataMapConsignment = array(
//        'OrderID'     => 'ttn',
        'route'         => 'route',
        'contact'       => 'contact',
        'type_delivery' => 'type_delivery',
        'status_text'   => 'status_text',
        'num_places'    => 'num_places',
        'volume'        => 'volume',
        'arrival_date'  => 'arrival_date',
        'payer'         => 'payer',
        'receiver_city' => 'receiver_city',
        'sender_city'   => 'sender_city',
        'sum'           => 'sum',
        'redelivery'    => 'redelivery',
        'OrderStatus'   => 'status'
    );

    private $_finalStatus = array(5);
    private $_consignmentsCollection = array();
    public $numConsignmentsUpdate = 0;

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
     * @return Testimonial_Intime_Model_Import
     */
    public function runWarehouseAndCityMassImport()
    {
        try {
            /** @var $apiClient Testimonial_Intime_Model_Api_Client */
            $apiClient = $this->getClientApi();

            Mage::helper('intime')->log('Start city import');
            $cities = $apiClient->getCityWarehouses();
            $this->_importCities($cities);
            Mage::helper('intime')->log('End city import');
            Mage::helper('intime')->log('Start warehouse import');
            $warehouses = $apiClient->getWarehouses();
            $this->_importWarehouses($warehouses);
            Mage::helper('intime')->log('End warehouse import');
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('intime')->log("Exception: \n" . $e->__toString());
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
    protected function _importCities($cities)
    {
        if (empty($cities)) {
            Mage::helper('intime')->log('No city with warehouses received');
            throw new Exception('No city with warehouses received');
        }
        $citiesXml = new SimpleXMLElement($cities);
        $cities = $this->_applyMap($citiesXml, $this->_dataMapCity);

        if (count($cities) > 0) {
            $cities = array_chunk($cities, $this->_bulkSizeCity);
            foreach ($cities as $chunk) {
                $sql = 'INSERT INTO `intime_city` (' . implode(', ', array_keys($chunk[0])) . ') VALUES ';
                foreach ($chunk as $cityToInsert) {
                    if(empty($cityToInsert))
                        continue;
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
            $existingCitiesTemp = Mage::getResourceModel('intime/city_collection')->getSelect();
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
        $i = 0;
        foreach ($apiObjects as $apiObject) {
            foreach ($apiObject as $apiKey => $value) {
                if (!isset($map[$apiKey])) {
                    continue;
                }
                $resultingArray[$i][$map[$apiKey]] = addcslashes((string)$value, "\000\n\r\\'\"\032");
            }
            if ($callback && method_exists($this, $callback)){
                $this->$callback($apiObject, $resultingArray[$i], $map);
            }
            $i++;
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
    protected function _importWarehouses($warehouses)
    {
        if (empty($warehouses)) {
            Mage::helper('intime')->log('No warehouses received');
            throw new Exception('No warehouses received');
        }

        $warehousesXml = new SimpleXMLElement($warehouses);

        $warehouses = $this->_applyWarehousesMap($warehousesXml, $this->_dataMapWarehouse);
        $existingWarehouses = $this->_getExistingWarehouses();
        $warehousesToDelete = array_diff(array_keys($existingWarehouses), array_keys($warehouses));

        if (count($warehousesToDelete) > 0) {
            $warehousesToDelete = implode(', ', $warehousesToDelete);
            $sql = "DELETE FROM `intime_warehouse` WHERE `id` IN ($warehousesToDelete)";
            $this->_getConnection()->query($sql);
            Mage::helper('intime')->log("Warehouses deleted: $warehousesToDelete");
        }

        if (count($warehouses) > 0) {
            $warehouses = array_chunk($warehouses, $this->_bulkSizeWarehouse);
            foreach ($warehouses as $chunk) {
                $sql = 'INSERT INTO `intime_warehouse` (' . implode(', ', array_keys($chunk[0])) . ') VALUES ';
                foreach ($chunk as $warehouseToInsert) {
                    $sql .= '("' . implode('", "', $warehouseToInsert) . '"), ';
                }
                $sql = trim($sql, ', ');
                $sql .= ' ON DUPLICATE KEY UPDATE ';
                foreach (array_keys($chunk[0]) as $field) {
                    $sql .= "$field = VALUES($field), ";
                }
                $sql = trim($sql, ', ');
                Mage::helper('intime')->log($sql);
                $this->_getConnection()->query($sql);
            }
        }

        return true;
    }

    protected function _applyWarehousesMap(SimpleXMLElement $apiObject, $map, $callback = null)
    {
        $resultingArray = array();
        $i = 0;
        foreach ($apiObject as $objCity) {
            $city = $this->_getCityIdByName($objCity->CityName);
            foreach ($objCity->Warehouse as $warehouse) {
                foreach ($warehouse as $key => $value) {
                    if (!isset($map[$key])) {
                        continue;
                    }
                    $resultingArray[$i]['city_code'] = $city->getId();
                    $resultingArray[$i][$map[$key]] = addcslashes((string)$value, "\000\n\r\\'\"\032");
                }
                if ($callback && method_exists($this, $callback)){
                    $this->$callback($apiObject, $resultingArray[$i], $map);
                }
                $i++;
            }
        }
        return $resultingArray;
    }

    protected function _getCityIdByName($name){
        return Mage::getModel('intime/city')->loadByName($name);
    }


    /**
     * @return array
     */
    protected function _getExistingWarehouses()
    {
        if (!$this->_exisitngWarehouses) {
            $existingWarehousesTemp = Mage::getResourceModel('intime/warehouse_collection')->getSelect();
            $existingWarehousesTemp = $this->_getConnection()->query($existingWarehousesTemp)->fetchAll();

            if (empty($existingWarehousesTemp)) {
                $existingWarehousesTemp = array();
            }

            $this->_exisitngWarehouses = array();
            foreach ($existingWarehousesTemp as $existingWarehouse) {
                $this->_exisitngWarehouses[] = $existingWarehouse;
            }

            unset($existingWarehousesTemp);
        }
        return $this->_exisitngWarehouses;
    }

    public function runCheckStatusConsignments()
    {
        try {
            $consignmentsCollection = $this->getConsignmentCollection();

            foreach ($consignmentsCollection as $consignment) {
                Mage::helper('intime')->log('Start consignment status synchronization.');
                $this->updateConsignment($consignment);
                Mage::helper('intime')->log('End consignment status synchronization.');
                $consignment->save();
            }

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('intime')->log("Exception: \n" . $e->__toString());
            throw $e;
        }
    }

    /**
     *
     * @param Testimonial_Intime_Model_Consignment $consignment
     * @return Testimonial_Intime_Model_Consignment
     */
    public function updateConsignment(Testimonial_Intime_Model_Consignment $consignment)
    {
        try {
            if ($consignment->canUpdate()) {
                $apiClient = $this->getClientApi();
                $ttn = $this->normalizeConsignmentTtn($consignment->getTtn());
                if (!is_null($consignment->getTtn())) {
                    $consignmentData = $this->_applyConsignmentMapSoap($apiClient->getConsignmentStatusSoap($ttn), $this->_dataMapConsignment);
                    $consignment->addData($consignmentData);
                    $consignmentData = $this->_applyConsignmentMap($this->_parseContent($apiClient->getConsignmentStatus($ttn)), $this->_dataMapConsignment);
                    $consignment->addData($consignmentData);
                    $this->numConsignmentsUpdate++;
                    if ($consignment->dataHasChangedFor('redelivery')){
                        $redelivery     = Mage::getModel('intime/consignment');
                        $redeliveryData = $this->_applyConsignmentMapSoap($apiClient->getConsignmentStatusSoap($consignmentData['redelivery']), $this->_dataMapConsignment);
                        $redelivery->addData($redeliveryData);
                        $redeliveryData = $this->_applyConsignmentMap($this->_parseContent($apiClient->getConsignmentStatus($consignmentData['redelivery'])), $this->_dataMapConsignment);
                        $redelivery->addData($redeliveryData);
                        $redelivery->setOrderId($consignment->getOrderId());
                        $redelivery->setOrderIncrementId($consignment->getOrderIncrementId());
                        $redelivery->setCustomerId($consignment->getCustomerId());
                        $redelivery->setShipmentId($consignment->getShipmentId());
                        $redelivery->setTtn($consignment->getRedelivery());
                        $redelivery->setIsBackDelivery(self::BACK_DELIVERY);
                        $redelivery->save();
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('intime')->log("Exception: \n" . $e->__toString());
            throw $e;
        }
        return $consignment;
    }

    public function normalizeConsignmentTtn($ttn)
    {
        $postfix = mb_substr($ttn, (mb_strlen($ttn, 'UTF-8') - 2), 2, 'UTF-8');

        if (mb_strtolower($postfix, 'UTF-8') == 'пс') {
            $ttn = mb_substr($ttn, 0, (mb_strlen($ttn, 'UTF-8') - 2), 'UTF-8');
        }
        return $ttn;
    }
    /**
     *
     * @param Zend_Http_Response $content
     * @return \Varien_Object
     */
    protected function _parseContent(Zend_Http_Response $content)
    {
        $page = $content->getBody();
        preg_match('/<span class="orange">([^<]*)/', $page, $ttn);
        preg_match('/<span class="navy">Маршрут груза:<\/span> <span class="orange">([^<]*)/', $page, $route);
        preg_match('/<span class="navy">Контакты:<\/span>\s*<span class="orange">([^<]*)/', $page, $contact);
        preg_match('/<span class="navy">Вид перевозки:<\/span> (.*)/', $page, $typeDelivery);
        preg_match('/<span class="navy">Статус доставки:<\/span>\s*<span class="orange">([^<]*)/', $page, $status);
        preg_match('/<span class="navy">Количество мест:<\/span>\s*<span class="orange">(\d+)/', $page, $numPlaces);
        preg_match('/<span class="navy">Объем:<\/span>\s<span class="orange">([^<]*)/', $page, $volume);
        preg_match('/<span class="navy">Дата прибытия груза на склад получения:<\/span>\s*<span class="orange">([^<]*)/', $page, $arrivalDate);
        preg_match('/<span class="navy">Сумма к оплате:<\/span>\s?<span class="orange">(\d+,?\d*)/', $page, $sum);
        preg_match('/<span class="navy">Оплачивает:<\/span>\s?<span class="orange">([^<]*)/', $page, $payer);
        preg_match('/<span class="navy">Обратная декларация:<\/span>\s?<span class="orange">([^<]*)/', $page, $redelivery);
        unset($page);
        if (isset($route[1]))
            $route = explode('-', $route[1]);
        $result = new Varien_Object();
        if (isset($route[0]))
            $result->setSenderCity(trim(strip_tags($route[0])));
        if (isset($route[1]))
            $result->setReceiverCity(trim(strip_tags($route[1])));
        if (isset($ttn[1]))
            $result->setTtn(trim(strip_tags($ttn[1])));
        if (isset($contact[1]))
            $result->setContact(trim(strip_tags($contact[1])));
        if (isset($typeDelivery[1]))
            $result->setTypeDelivery(trim(strip_tags($typeDelivery[1])));
        if (isset($status[1]))
            $result->setStatusText(trim(strip_tags($status[1])));
        if (isset($numPlaces[1]))
            $result->setNumPlaces(trim(strip_tags($numPlaces[1])));
        if (isset($volume[1]))
            $result->setVolume(trim(strip_tags($volume[1])));
        if (isset($arrivalDate[1]))
            $result->setArrivalDate(strtotime(trim(strip_tags($arrivalDate[1]))));
        if (isset($sum[1]))
            $result->setSum(trim(strip_tags($sum[1])));
        if (isset($payer[1]))
            $result->setPayer(trim(strip_tags($payer[1])));
        if (isset($redelivery[1]))
            $result->setRedelivery(trim(strip_tags($redelivery[1])));

        Mage::helper('intime')->log($result->toString());
        return $result;
    }

    /**
     *
     * @return Testimonial_Intime_Model_Api_Client
     * @throws Exception
     */
    public function getClientApi()
    {
        $apiUserId = Mage::helper('intime')->getApiUserId();
        $apiKey = Mage::helper('intime')->getApiKey();
        $apiUri = Mage::helper('intime')->getApiUri();
        if (!$apiKey || !$apiUri || !$apiUserId) {
            Mage::helper('intime')->log('No API key, API URL or api user id configured');
            throw new Exception('No API key or API URL configured');
        }

        return Mage::getSingleton('intime/api_client', array($apiUri, $apiKey, $apiUserId));
    }

    /**
     *
     * @return Testimonial_Intime_Model_Resource_Consignment_Collection
     */
    public function getConsignmentCollection()
    {
           $consignmentsCollection = Mage::getResourceModel('intime/consignment_collection')
                    ->addFieldToFilter('status', array(array('nin' => $this->_finalStatus), array('null' => true)))
                    ->addFieldToFilter('ttn', array('notnull' => true));
//          $consignmentsCollection->printLogQuery(true);die;
          return $consignmentsCollection;
    }

    /**
     *
     * @param SimpleXMLElement $apiObject
     * @param type $map
     * @param type $originalTTN
     * @return array
     */
    protected function _applyConsignmentMapXml($apiObject, $map)
    {
        $consignmentXml = new SimpleXMLElement($apiObject);
        $resultingArray = array();
        foreach ($consignmentXml as $apiKey => $value) {
            if (!isset($map[$apiKey])) {
                continue;
            }
            $resultingArray[$map[$apiKey]] = addcslashes((string)$value, "\000\n\r\\'\"\032");
        }

        return $resultingArray;
    }
    /**
     *
     * @param Varien_Object $apiObject
     * @param array $map
     * @return array
     */
    protected function _applyConsignmentMap(Varien_Object $apiObject, $map)
    {
        $resultingArray = array();
        foreach ($apiObject->getData() as $key => $value) {
            if (!isset($map[$key])) {
                continue;
            }
            $resultingArray[$map[$key]] = addcslashes((string)$value, "\000\n\r\\'\"\032");
        }
        return $resultingArray;
    }

    /**
     *
     * @param string $apiObject
     * @param array $map
     * @return array
     */
    protected function _applyConsignmentMapSoap($apiObject, $map)
    {
        $consignmentXml = new SimpleXMLElement($apiObject);
        $resultingArray = array();
        foreach ($consignmentXml as $apiKey => $value) {
            if (!isset($map[$apiKey])) {
                continue;
            }
            $resultingArray[$map[$apiKey]] = addcslashes((string)$value, "\000\n\r\\'\"\032");
        }

        return $resultingArray;
    }
}
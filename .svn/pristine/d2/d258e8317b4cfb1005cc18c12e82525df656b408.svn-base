<?php

class MageDoc_OrderExport_Model_Export_Product_1c extends MageDoc_OrderExport_Model_Export_Order_Abstract
{
    const PRODUCT_EXPORT_FILE = 'catalog.xml';
    const PRODUCT_LIMIT = 1000;

    static protected $_classifierMap;
    static protected $_catalogMap;
    static protected $_categoryMap;
    static protected $_productMap;
    static protected $_ownerMap;
    static protected $_offerPackageMap;
    static protected $_offerMap;

    protected $_writeEmptyValues = false;
    protected $_retailer = null;

    protected function _initWriteAdapter($fileName)
    {
        $adapter = Mage::getModel('magedoc_orderexport/export_adapter_simpleXml', array($fileName));
        self::log("Export: Strating output to file $fileName");
        return $adapter;
    }

    public function exportCatalog()
    {
        try {
            self::log('Products Export Started');
            //$products = $this->getProductsToExport(self::ORDER_EXPORT_STATUS_ADDRESS_EXPORTED,$this->_getConfigData('product_export_limit'));
            $products = $this->getProductsToExport(null, self::PRODUCT_LIMIT);
            $prices = clone $products;
            $categories = Mage::getResourceModel('catalog/category_flat_collection')
                //->joinAttribute('name', 'category/name')
                ->addAttributeToSelect('name')
                ->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
            $categories->fetchItem();

            if (!count($products)) {
                self::log('No products to export');
                return $this;
            }

            $exportFileName = $this->_getConfigData('product_export_filename');
            $exportFileName = $exportFileName ?
                $exportFileName :
                self::PRODUCT_EXPORT_FILE;

            $oldData = $this->_getOldData($exportFileName);

            $write = $this->_getWriteAdapter($exportFileName);
            $write->startDocument('1.0','UTF-8');
            $write->startElement('КоммерческаяИнформация');
            $write->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $write->writeAttribute('xsi:noNamespaceSchemaLocation', 'commerceml_2.06.xsd');
            $write->writeAttribute('ВерсияСхемы', '2.06');
            $write->writeAttribute('ДатаФормирования', '2014-08-30T18:14:34');

            if (!empty($oldData)) {
                $write->writeRaw($oldData);
            }

            $retailer = new Varien_Object();
            $retailer->setData(
                array(
                    'retailer_id'   => '000000001',
                    'name'          => 'АвтоТО - Литвин',
                    'address'       => 'Харьковская обл, Харьков, Юрьевская, дом № 17а',
                    'complete_name' => 'ФОП Литвин С.А.',
                    'pin'           => '3101606516',
                    'currency_code' => 'UAH',
                    'catalog_id'    => 'bd72d8f9-55bc-11d9-848a-00112f43529a',
                    'classifier_id' => 'bd72d8f9-55bc-11d9-848a-00112f43529a',
                    )
            );
            $retailer->setProducts($products);
            $retailer->setPrices($prices);
            $retailer->setCategories($categories);
            $this->_retailer = $retailer;

            $this->_writeMappedData($write,$retailer,self::getClassifierMap());
            $this->_writeMappedData($write,$retailer,self::getCatalogMap());
            $this->_writeMappedData($write,$retailer,self::getOfferPackageMap());

            $retailers = Mage::getResourceModel('magedoc/retailer_collection')
                ->addFieldToFilter('is_import_enabled', 1);
            if ($this->getRetailerId()){
                $retailers->addFieldToFilter('retailer_id', $this->getRetailerId());
            }

            $productIds = $products->getAllIds(self::PRODUCT_LIMIT);

            while ($retailer = $retailers->fetchItem()){
                $this->_retailer = $retailer;
                $offers = $this->getOffersToExport($retailer, null, self::PRODUCT_LIMIT, $productIds);
                $retailer->setCompleteName($retailer->getName());
                $retailer->setCatalogId('bd72d8f9-55bc-11d9-848a-00112f43529a');
                $retailer->setClassifierId('bd72d8f9-55bc-11d9-848a-00112f43529a');
                $retailer->setPrices($offers);
                $this->_writeMappedData($write,$retailer,self::getOfferPackageMap());
            }

            $write->endElement();
            $write->endDocument();

            //$this->_updateExportedOrderStatuses();

            self::log(count($products)." products were exported to file: $exportFileName");
        }catch (Exception $e) {
            self::log('Products Export failed: '.$e->getMessage());
            Mage::logException($e);
        }
    }

    public function getProductsToExport($excludeExportStatus = null, $limit = null, $retailerId = null)
    {
        $stores = explode(',', $this->_getConfigData('process_store'));
        $adminStore = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;

        $products = Mage::getResourceModel('magedoc_orderexport/catalog_product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->joinAttribute('name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter(1)
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        if (!is_null($retailerId)){
            $products->addAttributeToFilter('retailer_id', $retailerId);
        }

        $select = $products->getSelect();
        $select->join(
            array('cp' => $products->getResource()->getTable('catalog/category_product')),
            'e.entity_id = cp.product_id',
            array('category_id', 'qty' => new Zend_Db_Expr('1'))
        );

        $select->group('e.entity_id');

        if (!is_null($excludeExportStatus)) {
            $products->getSelect()->where('NOT export_status & ? OR export_status IS NULL', $excludeExportStatus);
        }

        if ($limit) {
            $select->limit($limit);
            $products->setPage(1, $limit);
        }

        return $products;
    }

    public function getOffersToExport($retailer, $excludeExportStatus = null, $limit = null, $productIds = array())
    {
        $stores = explode(',', $this->_getConfigData('process_store'));
        $adminStore = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;

        $products = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        $select = $products->getSelect();
        $select->join(
            array('ird' => $products->getResource()->getTable('magedoc/import_retailer_data')),
            "e.entity_id = ird.product_id AND ird.retailer_id = {$retailer->getId()}",
            array('final_price' => 'cost', 'qty')
        );

        if (!empty($productIds)){
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $select->group('e.entity_id');

        if (!is_null($excludeExportStatus)) {
            $products->getSelect()->where('NOT export_status & ? OR export_status IS NULL', $excludeExportStatus);
        }

        if ($limit)
        {
            $select->limit($limit);
            $products->setPage(1, $limit);
        }

        return $products;
    }

    static public function getClassifierMap()
    {
        if (!isset(self::$_classifierMap)) {
            self::$_classifierMap = array(
                'Классификатор' => array(
                    'Ид'                => '@classifier_id',
                    'Наименование'      => 'Классификатор Каталог товаров',
                    '@@include_getOwnerMap' => '.',
                    'Группы'    => array(
                        '@@each_getCategoryMap'  =>  'getCategories()'
                    ),
                    'ТипыЦен'   =>  array(
                        'ТипЦены'   => array(
                            'Ид'  =>  'Опт',
                            'Наименование'  =>  'Опт',
                            'Валюта'        => '@currency_code'
                        )
                    ),
                )
            );
        }
        return self::$_classifierMap;
    }

    static public function getCategoryMap()
    {
        if (!isset(self::$_categoryMap)) {
            self::$_categoryMap = array(
                'Группа'          => array(
                    'Ид'                => '@entity_id',
                    'Наименование'      => '@name',
                )
            );
        }
        return self::$_categoryMap;
    }

    static public function getProductMap()
    {
        if (!isset(self::$_productMap)) {
            self::$_productMap = array(
                //'@@skipNext' => '::_isShippingFree(@)',
                'Товар' => array(
                    'ИдентификаторТовара'   => array(
                        'Ид'            => '@sku',
                        'Штрихкод'      => 'getEan()',
                        'Артикул'       => '@sku',
                        ),
                    'Артикул'       => '@sku',
                    'Наименование'  => '@name',
                    'БазоваяЕдиница' => array(
                        '@Код'                      => "796",
                        '@НаименованиеПолное'       => "Штука",
                        '@МеждународноеСокращение'  => "PCE",
                        '@textContent'              => 'шт'
                    ),
                    'Группы' => array(
                        'Ид'    =>  '@category_id'
                    ),
                    'Описание' => '@short_description',
                    'Картинка' => '',
                    'Производитель' => array(
                        'ТорговаяМарка' => 'getManufacturerText()'
                    ),
                    'СтавкиНалогов' => array(
                        'СтавкаНалога' => array(
                            'Наименование'  => 'НДС',
                            'Ставка'        => '18'
                        )
                    ),
                    'ХарактеристикиТовара'  => array(
                    ),
                    'ЗначенияРеквизитов'    => array(
                        'ЗначениеРеквизита'     => array(
                            'Наименование'          => 'ВидНоменклатуры',
                            'Значение'              => 'Товар'
                        ),
                        'ЗначениеРеквизита__1'     => array(
                            'Наименование'          => 'ТипНоменклатуры',
                            'Значение'              => 'Товар'
                        ),
                        'ЗначениеРеквизита__2'     => array(
                            'Наименование'          => 'Полное наименование',
                            'Значение'              => '@name'
                        )
                    ),
                )
            );
        }
        return self::$_productMap;
    }

    static public function getCatalogMap()
    {
        if (!isset(self::$_catalogMap)) {
            self::$_catalogMap = array(
                'Каталог' => array(
                    '@СодержитТолькоИзменения' => 'false',
                    'Ид'                => '@catalog_id',
                    'ИдКлассификатора'  => '@classifier_id',
                    'Наименование'      => 'Каталог товаров',
                    '@@include_getOwnerMap' => '.',
                    'Товары'    => array(
                        '@@each_getProductMap'  =>  'getProducts()'
                    )
                )
            );
        }
        return self::$_catalogMap;
    }

    static public function getOfferPackageMap()
    {
        if (!isset(self::$_offerPackageMap)) {
            self::$_offerPackageMap = array(
                'ПакетПредложений' => array(
                    '@СодержитТолькоИзменения' => 'false',
                    'ИдКаталога'        => '@catalog_id',
                    'ИдКлассификатора'  => '@classifier_id',
                    'Наименование'      => 'Предложения товаров',
                    '@@include_getOwnerMap' => '.',
                    'ТипыЦен'   =>  array(
                        'ТипЦены'   => array(
                            'Ид'  =>  'Опт-{{@name}}-{{@currency_code}}',
                            'Наименование'  =>  'Опт - {{@complete_name}} - {{@currency_code}}',
                            'Валюта'        => '@currency_code'
                        )
                    ),
                    'Предложения'    => array(
                        '@@each_getOfferMap'  =>  'getPrices()'
                    )
                )
            );
        }
        return self::$_offerPackageMap;
    }

    static public function getOfferMap()
    {
        if (!isset(self::$_offerMap)) {
            self::$_offerMap = array(
                'Предложение' => array(
                    'ИдентификаторТовара'   => array(
                        'Ид'            => '@sku',
                        'Артикул'       => '@sku',
                    ),
                    'Артикул'       => '@sku',
                    'Количество' =>  '@qty',
                    //'Склад'      => '',
                    'Цены'    => array(
                        'Цена'  =>  array(
                            'ИдТипаЦены'    => 'Опт-{{::getRetailer()/@name}}-{{::getRetailer()/@currency_code}}',
                            'ЦенаЗаЕдиницу' => 'getFinalPrice()',
                            'Валюта'        => '{{::getRetailer()/@currency_code}}'
                        )
                    ),
                    'БазоваяЕдиница' => array(
                        '@Код'                      => "796",
                        '@НаименованиеПолное'       => "Штука",
                        '@МеждународноеСокращение'  => "PCE",
                        '@textContent'              => 'шт'
                    ),
                )
            );
        }
        return self::$_offerMap;
    }

    static public function getOwnerMap()
    {
        if (!isset(self::$_ownerMap)) {
            self::$_ownerMap = array(
                'Владелец'          => array(
                    'Ид'                => '@retailer_id',
                    'Наименование'      => '@name',
                    'ОфициальноеНаименование' => '@complete_name',
                    'ЮридическийАдрес'  => array(
                        'Представление'     => '@address'
                    ),
                    'ИНН'               => '@pin',
                    //'КПП'               => '567892222',
                    'РасчетныеСчета'    => array(
                        //'@@each_getAccountMap'         => 'getAccounts()'
                    )
                )
            );
        }
        return self::$_ownerMap;
    }

    protected function _getFilePath($fileName)
    {
        return $this->_getDataDir() . $this->getRelativeExportPath() . $fileName;
    }

    protected function _afterExportOrder($adapter, &$orders)
    {
        if ($this->_getConfigData('create_shipment')){
            foreach($orders as $order){
                $this->_createShipment($order);
            }
        }
        $this->ftpUploadFile();
    }

    public function getRetailer()
    {
        return $this->_retailer;
    }
}
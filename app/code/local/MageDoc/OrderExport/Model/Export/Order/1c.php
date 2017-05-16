<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   MageDoc
 * @package    MageDoc_OrderExport
 * @copyright  Copyright (c) 2015 MageDoc LTD (http://www.magedoc.net)
 */
class MageDoc_OrderExport_Model_Export_Order_1c extends MageDoc_OrderExport_Model_Export_Order_Abstract
{

    protected $_writeEmptyValues = false;

    static protected $_orderShipmentItemMap;
    
    protected function _initWriteAdapter($fileName)
    {
        $adapter = Mage::getModel('magedoc_orderexport/export_adapter_simpleXml', array($fileName));
        self::log("Export: Strating output to file $fileName");
        return $adapter;
    }

    protected function _beforeExportOrder($writeAdapter, $oldData)
    {
        $writeAdapter->startDocument('1.0','UTF-8');
        $writeAdapter->startElement('КоммерческаяИнформация');
        $writeAdapter->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $writeAdapter->writeAttribute('xsi:noNamespaceSchemaLocation', 'commerceml_2.06.xsd');
        $writeAdapter->writeAttribute('ВерсияСхемы', '2.06');
        $writeAdapter->writeAttribute('ДатаФормирования', '2013-10-23T18:13:34');
    }

    protected function _exportOrder($order, $writeAdapter)
    {
        $this->_writeMappedData($writeAdapter, $order, self::getOrderAttributesMap());
        return $this;
    }

    static public function getOrderShipmentItemMap()
    {
        if (!isset(self::$_orderShipmentItemMap)) {
            self::$_orderShipmentItemMap = array(
                'Товар' => array(
                    'Ид' => 'ORDER_DELIVERY',
                    'Наименование' => 'Доставка заказа',
                    'БазоваяЕдиница' => array(
                        '@Код'                      => "796",
                        '@НаименованиеПолное'       => "Штука",
                        '@МеждународноеСокращение'  => "PCE",
                        '@textContent'              => 'шт'
                    ),
                    'ЦенаЗаЕдиницу' => ':round(@shipping_amount, 2)',
                    'Количество' => '1',
                    'Сумма' => ':round(@shipping_amount, 2)',
                    'ЗначенияРеквизитов__1' => array(
                        'ЗначениеРеквизита' => array(
                            'Наименование' => 'ВидНоменклатуры',
                            'Значение' => 'Услуга',
                        ),
                        'ЗначениеРеквизита__2' => array(
                            'Наименование' => 'ТипНоменклатуры',
                            'Значение' => 'Услуга',
                        ),
                    ),
                ),
            );
        }
        return self::$_orderShipmentItemMap;
    }

    protected function _getOrdersCollectionResourceModelName()
    {
        return 'magedoc/order_collection';
    }

    static public function getOrderItemMap()
    {
        if (!isset(self::$_orderItemAttributesMap)) {
            self::$_orderItemAttributesMap = array(
                'Товар' => array(
                    'ИдентификаторТовара'   => array(
                        'Ид'            => '@sku',
                        'Артикул'       => '@sku',
                    ),
                    //??
                    'Ид'            => '@sku',
                    //??
                    'Артикул'       => '@sku',
                    'ИдКаталога'    => 'bd72d8f9-55bc-11d9-848a-00112f43529a',
                    'ИдКлассификатора' => 'bd72d8f9-55bc-11d9-848a-00112f43529a',
                    'Наименование'  => '::getShortName(@)',
                    'НаименованиеПолное'  => 'getName()',
                    'БазоваяЕдиница' => array(
                        '@Код'                      => "796",
                        '@НаименованиеПолное'       => "Штука",
                        '@МеждународноеСокращение'  => "PCE",
                        '@textContent'              => 'шт'
                    ),
                    'ЕдиницаИзмерения' => array(
                        'Единица'       => 'шт'
                    ),
                    'ЦенаЗаЕдиницу' => '@price',
                    'Количество'    => '@qty_ordered',
                    'Сумма'         =>  '::calcRowTotal(@row_total_incl_tax,@discount_amount)',
                    'ЗначенияРеквизитов__1' => array(
                        'ЗначениеРеквизита' => array(
                            'Наименование' => 'ВидНоменклатуры',
                            'Значение' => 'Запчасть',
                        ),
                        'ЗначениеРеквизита__2' => array(
                            'Наименование' => 'ТипНоменклатуры',
                            'Значение' => 'Товар',
                        ),
                    ),
                ),
            );
        }
        return self::$_orderItemAttributesMap;
    }

    static public function getOrderAttributesMap()
    {
        if (!isset(self::$_orderAttributesMap)) {
            self::$_orderAttributesMap = array(
                'Документ' => array(
                    'Ид'        => '@increment_id',
                    'Номер'     => '@increment_id',
                    'Дата'      => ':substr(@created_at,0,10)',
                    'ХозОперация' => 'Заказ товара',
                    'Роль'      => 'Продавец',
                    'Валюта'    => '@order_currency_code',
                    'Курс'      => '@base_to_order_rate',
                    //'@transaction_id'   =>  'getPayment()/@last_trans_id',
                    'Сумма'     => '@grand_total',
                    'Контрагенты'   => array(
                        'Контрагент'    => array(
                            'Ид'            => '@customer_id',
                            'Наименование'  => '@customer_email',
                            'Роль'          => 'Покупатель',
                            'ПолноеНаименование' => 'getCustomerName()',
                            'Фамилия'       => '@customer_lastname',
                            'Имя'           => '@customer_firstname',
                            'АдресРегистрации' => array(
                                'Представление' => 'getBillingAddress()/format(short)',
                                'АдресноеПоле__1' => array(
                                    'Тип'           => 'Почтовый индекс',
                                    'Значение'      => 'getBillingAddress()/@postcode',
                                ),
                                'АдресноеПоле__2' => array(
                                    'Тип'           => 'Улица',
                                    'Значение'      => 'getBillingAddress()/@street',
                                ),
                            ),
                            'Контакты' => '',
                            'Представители' => array(
                                'Представитель' => array(
                                     'Контрагент' => array(
                                        'Отношение' => 'Контактное лицо',
                                        'Ид' => '@customer_id',
                                        'Наименование' => 'getBillingAddress()/getName()'
                                    )
                                )
                            ),
                        ),
                    ),
                    'Время'      => ':substr(@created_at,11,8)',
                    'Комментарий' => '@last_status_history_comment',
                    'Товары'    => array(
                        '@@skipNext' => '::isShippingFree(@)',
                        '@@include_getOrderShipmentItemMap'  => '.',
                        '@@each_getOrderItemMap'  => 'getAllItemsAndInquiries()',
                    ),
                    'ЗначенияРеквизитов' => array(
                        'ЗначениеРеквизита__1' => array(
                            'Наименование' => 'Метод оплаты',
                            //'Значение' => 'Наличный расчет',
                            'Значение' => 'getPayment()/@method',
                        ),
                        'ЗначениеРеквизита__2' => array(
                            'Наименование' => 'Заказ оплачен',
                            'Значение' => 'false',
                        ),
                        'ЗначениеРеквизита__3' => array(
                            'Наименование' => 'Доставка разрешена',
                            'Значение' => 'false',
                        ),
                        'ЗначениеРеквизита__4' => array(
                            'Наименование' => 'Отменен',
                            'Значение' => 'false',
                        ),
                        'ЗначениеРеквизита__5' => array(
                            'Наименование' => 'Финальный статус',
                            'Значение' => 'false',
                        ),
                        'ЗначениеРеквизита__6' => array(
                            'Наименование' => 'Статус заказа',
                            //'Значение' => '[N] Принят',
                            'Значение' => '@status',
                        ),
                        'ЗначениеРеквизита__7' => array(
                            'Наименование' => 'Дата изменения статуса',
                            'Значение' => '@updated_at',
                        )
                    )
                )
            );
        }
        return self::$_orderAttributesMap;
    }

    protected function _getFilePath($fileName)
    {
        return 'php://output';
        return $this->_getDataDir() . $this->getRelativeExportPath() . $fileName;
    }

    protected function _afterExportOrder($writeAdapter, &$orders)
    {
        $writeAdapter->endElement();
        $writeAdapter->endDocument();

        if ($this->_getConfigData('create_shipment')){
            foreach($orders as $order){
                $this->_createShipment($order);
            }
        }
        $this->ftpUploadFile();
    }

    public function getShortName($row)
    {
        $manufacturer = explode('-', $row->getSku());
        $manufacturer = $manufacturer[0];
        return $manufacturer
            ? mb_substr($row->getName(), mb_strpos($row->getName(), $manufacturer))
            : $row->getName();
    }
}
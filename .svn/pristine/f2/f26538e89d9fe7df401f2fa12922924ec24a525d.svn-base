<?php
class Testimonial_MageDoc_Model_Retailer_Config_Supply
    extends Mage_Core_Model_Abstract
{
    const DEFAULT_ORDER_HOURS_END = '0000-00-00 00:00:00';
    const DELIVERY_TYPE_PICKUP = 'pickup';
    const DELIVERY_TYPE_DELIVERY_PLANNED = 'delivery_planned';
    const DELIVERY_TYPE_DELIVERY_FREE = 'delivery_free';
    const DELIVERY_TYPE_SHIPPING = 'shipping';
    const DEFAULT_DELIVERY_TYPE = self::DELIVERY_TYPE_PICKUP;

    protected $_retailer;

    protected function _construct()
    {
        $this->_init('magedoc/retailer_config_supply', 'retailer_id');
    }

    public function setRetailer(Testimonial_MageDoc_Model_Retailer $retailer)
    {
        $this->_retailer = $retailer;
    }
        
    public function getRetailer()
    {
        return $this->_retailer;
    }

    public function getDeliveryEstimationDate()
    {
        $deliveryDays = $this->getDeliveryTermDays();
        $date = Mage::app()->getLocale()->date();

        if (!$this->isWorkingDay($date) || !$this->isOrderAcceptingTime($date)){
            $this->addDays($date, 1, 'isDeliveryDay');
        }

        $this->addDays($date, $deliveryDays, 'isDeliveryDay');
        return $date;
    }

    public function getWorkingDays()
    {
        return array(1,2,3,4,5,6);
    }

    public function getDeliveryDays()
    {
        return array(1,2,3,4,5,6);
    }

    public function isWorkingDay($date)
    {
        return in_array($date->get(Zend_Date::WEEKDAY_DIGIT), $this->getWorkingDays())
            && !$this->isHoliday($date);
    }

    public function isDeliveryDay($date)
    {
        return in_array($date->get(Zend_Date::WEEKDAY_DIGIT), $this->getDeliveryDays())
            && !$this->isHoliday($date);
    }

    public function isHoliday($date)
    {
        return false;
    }

    public function isOrderAcceptingTime($date)
    {
        if(is_null($this->getOrderHoursEnd())) {
            return false;
        }
        $orderHoursEnd = Mage::helper('magedoc/supply')->getDate()
            ->set($this->getOrderHoursEnd());

        return $date->compare($orderHoursEnd, Zend_Date::TIMES) == -1;
    }

    public function getOrderHoursEnd()
    {
        return $this->hasData('order_hours_end')
            ? $this->getData('order_hours_end')
            : self::DEFAULT_ORDER_HOURS_END;
    }

    public function addDays($date, $count, $dayCheckCallback = null)
    {
        $i = 0;
        $limit = 365;
        $days = 0;
        if ($count){
            while ($days < $count && $i++ < $limit){
                $date->add(1, Zend_Date::DAY);
                if (!$dayCheckCallback || (method_exists($this, $dayCheckCallback) && $this->$dayCheckCallback($date))){
                    $days++;
                }
            }
        }
        return $date;
    }

    public function getDeliveryType()
    {
        return $this->hasData('delivery_type')
            ? $this->getData('delivery_type')
            : self::DEFAULT_DELIVERY_TYPE;
    }

    protected function _parseTimeFromMysql($mysqlTime)
    {
        $time = explode(' ', $mysqlTime);
        return str_replace(':', ',', $time[1]);
    }

    protected function _beforeSave()
    {
        $date = $this->getOrderHoursEnd();
        if (!$date instanceof Zend_Db_Expr){
            $this->setOrderHoursEnd(new Zend_Db_Expr("'{$date}'"));
        }
    }

    protected function _afterLoad()
    {
        if(isset($this->_data['order_hours_end'])) {
            $this->_data['order_hours_end_formatted'] = $this->_parseTimeFromMysql( $this->_data['order_hours_end']);
        }
        parent::_afterLoad();
    }

    public function setOrderHoursEndFormatted( $hours )
    {
        $formatted = '0000-00-00 ' . implode(':', $hours);
        $this->setOrderHoursEnd($formatted);
        $this->setData('order_hours_end_formatted', implode(',', $hours));

        return $this;
    }

}

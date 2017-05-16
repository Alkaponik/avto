<?php
class Testimonial_MageDoc_Model_Customer_Vehicle extends Mage_Core_Model_Abstract
{
    protected $_mapFormatData = array(
        'manufacturer',
        'production_start_year',
        'model',
        'type',
        'mileage',
        'vin'
        );
    public function _construct()
    {
        $this->_init('magedoc/customer_vehicle');
    }
    public function getFormatHtml()
    {
        $formater   = new Varien_Filter_Template();
        $format = '{{var manufacturer}} {{var production_start_year}} {{var model}}'
            . '<br/>{{var type}}<br/>{{var mileage}}<br/>{{var vin}}';

        $vehicleData = $this->getData();
        $data = array();
        foreach($this->_mapFormatData as $item){
            $data[$item] = isset($vehicleData[$item])? $vehicleData[$item]: '';
        }
        $formater->setVariables($data);
        return $formater->filter($format);
    }

    public function getFormatJs()
    {
        return '#{manufacturer} #{production_start_year} #{model}'
        . '<br/>#{type}<br/>#{mileage}<br/>#{vin}';
    }
}
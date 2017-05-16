<?php

class Testimonial_MageDoc_Model_Adminhtml_System_Config_Source_Date_Start
{
    const  PRODUCTION_START_YEAR_DEFAULT = 1950;
    
    public function toOptionArray($addEmpty = true)
    {
        $_storeCode = Mage::app()->getFrontController()->getAction()->getRequest()->getParam('store');
        $options = array();
        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select a Category --'),
                'value' => ''
            );
        }
        $currentDate = date("Y", Mage::getModel('core/date')->timestamp(time()));
        for($i = self::PRODUCTION_START_YEAR_DEFAULT; $i <= $currentDate; $i++){
            $options[] = array('value' => $i, 'label' => $i);
        }
        
        return $options;
    }
    
       
}
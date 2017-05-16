<?php

class Testimonial_MageDoc_Model_Adminhtml_System_Config_Source_Admin_User
{
    
    public function toOptionArray($addEmpty = true)
    {
        $_storeCode = Mage::app()->getFrontController()->getAction()->getRequest()->getParam('store');
        $options = array();
        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select Default User --'),
                'value' => ''
            );
        }
        $collection = Mage::getResourceModel('admin/user_collection');
        foreach($collection as $item){
            $options[] = array('label' => $item->getUsername(), 
                                    'value' => $item->getUserId());
        }
        
        return $options;
    }
    
       
}
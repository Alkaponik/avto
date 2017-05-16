<?php

class Testimonial_MageDoc_Block_Adminhtml_Vehicle extends Mage_Core_Block_Template 
        implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        //parent::__construct();
        $this->setTemplate('magedoc/vehicle/chooser.phtml');
    }
    
      /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return "My vehicle";
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return "Choose your vehicle";
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
    
    

    
    
}

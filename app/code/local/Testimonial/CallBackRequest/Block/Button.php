<?php
/**
 * @copyright   Copyright (c) 2011 http://magentosupport.net
 * @author		Vlad Vysochansky
 * @license     http://opensource.org/licenses/gpl-license.php  GNU General Public License (GPL)
 */
class Testimonial_CallBackRequest_Block_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = "http://www.magentosupport.net/contact";

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel(Mage::helper('callbackrequest')->__('Get support!'))
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}

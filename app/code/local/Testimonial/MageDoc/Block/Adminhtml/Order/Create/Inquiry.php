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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order create items block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Testimonial_MageDoc_Block_Adminhtml_Order_Create_Inquiry extends Mage_Adminhtml_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_form;
    protected $_element;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magedoc/order/create/inquiry.phtml');
        $this->setId('magedoc_order_create_inquiry');
        $this->_element = $this->_prepareInquiry();
    }

    protected function _prepareInquiry()
    {
      $this->_form = new Varien_Data_Form();

      $fieldset = $this->_form->addFieldset('inquiry', array('legend'=>Mage::helper('magedoc')->__('Customer Inquiry')));
      $fieldset->setRenderer(Mage::getBlockSingleton('magedoc/adminhtml_widget_form_renderer_fieldset'));
      $fieldset->setFieldSetContainerId('dummy');
      
      $fieldset->addType('chooser', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Chooser');
      $fieldset->addType('grid', 'Testimonial_MageDoc_Block_Adminhtml_Form_Element_Grid');

      $fieldset->addField('vehicle', 'chooser', array(
          'id' => 'test'
          ))->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_widget_chooser')
                  ->setData(array('id' => 'vehicle')));
      $checkbox = $fieldset->addField('is_filter', 'checkbox', array('label' => 'Filter'));
      $checkbox->getRenderer()->setTemplate('magedoc/widget/form/renderer/fieldset/element.phtml');
      $fieldset->addField('inquiries', 'grid', array())
        ->setRenderer(Mage::app()->getLayout()->createBlock('magedoc/adminhtml_order_create_inquiry_grid'));     
      
      return $fieldset;  
    }
    
    public function getForm()
    {
        return $this->_form;
    }
    
    public function getElement()
    {
        return $this->_element;
    }

    
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

}

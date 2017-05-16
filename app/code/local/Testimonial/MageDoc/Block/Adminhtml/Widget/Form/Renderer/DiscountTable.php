<?php

class Testimonial_MageDoc_Block_Adminhtml_Widget_Form_Renderer_DiscountTable
    extends MageDoc_System_Block_Adminhtml_Config_Field_Array
{

    public function _prepareToRender()
    {
        $this->addColumn('lower_limit',
            array(
                'label' => Mage::helper('magedoc')->__('Lower limit'),
                'style' => 'width:90px',
            )
        );

        $this->addColumn('value',
            array(
                'label' => Mage::helper('magedoc')->__('Value'),
                'style' => 'width:90px',
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('magedoc')->__('Add Stage');
    }
}

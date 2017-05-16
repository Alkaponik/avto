<?php

class Testimonial_MageDoc_Block_Adminhtml_Permission_Editroles extends Mage_Adminhtml_Block_Permissions_Editroles
{

    protected function _prepareLayout()
    {
        $result = parent::_prepareLayout();
        $role = Mage::registry('current_role');
        $this->addTab('magedoc_supply', array(
            'label'     => Mage::helper('magedoc')->__('Supply Permissions'),
            'title'     => Mage::helper('magedoc')->__('Supply Permissions'),
            'content'   => $this->getLayout()->createBlock('magedoc/adminhtml_permission_tab_supply')->setRole($role)->toHtml(),
        ));
        return $result;
    }
}

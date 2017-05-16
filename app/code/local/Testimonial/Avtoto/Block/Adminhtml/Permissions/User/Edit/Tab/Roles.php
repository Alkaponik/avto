<?php

class Testimonial_Avtoto_Block_Adminhtml_Permissions_User_Edit_Tab_Roles extends Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Roles
{
    protected function _afterLoadCollection()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('system/acl/roles')){
            $collection  = $this->getCollection();
            foreach ($collection as $item){
                if (Mage::helper('avtoto')->isAllRolesAccessAllowed($item->getId())
                    || Mage::helper('avtoto')->isAllUsersAccessAllowed($item->getId())){
                    $collection->removeItemByKey($item->getId());
                }
            }
        }

        parent::_afterLoadCollection();
    }
}

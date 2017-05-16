<?php

class Testimonial_Avtoto_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_XML_PATH_CREATE_CALL_ON_ORDER_ASSEMBLY = 'avtoto/general/create_call_on_order_assemly';

    public function isAllRolesAccessAllowed($groupId)
    {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            return false;
        }
        $acl = Mage::getSingleton('admin/session')->getAcl();
        $resource = 'admin/system/acl/roles';
        $aclRole = 'G'.$groupId;

        try {
            return $acl->isAllowed($aclRole, $resource);
        } catch (Exception $e) {
            try {
                if (!$acl->has($resource)) {
                    return $acl->isAllowed($aclRole, null);
                }
            } catch (Exception $e) {
            }
        }
        return false;
    }

    public function isAllUsersAccessAllowed($groupId)
    {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            return false;
        }
        $acl = Mage::getSingleton('admin/session')->getAcl();
        $resource = 'admin/system/acl/users';
        $aclRole = 'G'.$groupId;

        try {
            return $acl->isAllowed($aclRole, $resource);
        } catch (Exception $e) {
            try {
                if (!$acl->has($resource)) {
                    return $acl->isAllowed($aclRole, null);
                }
            } catch (Exception $e) {
            }
        }
        return false;
    }

    public function getCreateCrmCallOnOrderAssembly($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_CREATE_CALL_ON_ORDER_ASSEMBLY, $storeId);
    }
}
<?php

class MageDoc_CRM_Adminhtml_Crm_CustomerController extends Mage_Adminhtml_Controller_action
{
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'massMerge':
            default:
                $aclResource = 'customer/merge';
                break;
        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }

    public function massMergeAction()
    {
        $customerIds = $this->getRequest()->getParam('customer');
        if (!is_array($customerIds) || count($customerIds) < 2){
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select at least two cusotmers to merge'));
            $this->_redirect('*/customer');
            return;
        }
        arsort($customerIds);
        $destinationCustomerId = array_pop($customerIds);
        $hlp = Mage::getResourceHelper('magedoc_crm');
        foreach ($customerIds as $customerId){
            if ($customerId != $destinationCustomerId){
                $hlp->mergeCustomers($customerId, $destinationCustomerId);
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Customers %s were merged successfully', implode(',', $customerIds)));
        $this->_redirect('*/customer');
    }
}
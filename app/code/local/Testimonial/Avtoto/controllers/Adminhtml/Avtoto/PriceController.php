<?php

class Testimonial_Avtoto_Adminhtml_Avtoto_PriceController extends Mage_Adminhtml_Controller_Action
{
    public function updateAction()
    {

        try{
            /** @var Testimonial_Avtoto_Model_Price $priceModel */
            $priceModel = Mage::getSingleton('avtoto/price');

            $priceModel->updateShopPrice();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Price and retailer tables are synchronized.'));
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError( $e->getMessage() );
        }
        $this->_redirect('magedoc/adminhtml_retailer');
    }

    public function statusAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function reindexProcessAction()
    {
        $data = $this->getRequest()->getParams();

        Mage::getSingleton('avtoto/autopricing_process')->runProcess( $data['process_code'] );

        if(Mage::app()->getFrontController()->getAction()->getResponse()->isRedirect()) {
            return;
        }
        $this->_redirect('*/*/status');
        return;
    }



    public function massReindexProcessAction()
    {
        $data = $this->getRequest()->getParams();
        $processList = Mage::getSingleton('avtoto/autopricing_process')->getProcesses();

        if (isset($data['status_id'])) {
            foreach ($data['status_id'] as $statusId) {
                $status = Mage::getModel('avtoto/autopricing_process_status')->load($statusId);

                if (!$status->isObjectNew() && isset($processList[$status->getProcessCode()])) {
                    $status->addData(array('sort_order' => $processList[$status->getProcessCode()]['sort_order']));
                    $statuses[$statusId] = $status;
                }
            }

            uasort($statuses,
                function ($a, $b) {
                    if ($a->getSortOrder() == $b->getSortOrder()) {
                        return 0;
                    }
                    return ($a->getSortOrder() < $b->getSortOrder()) ? -1 : 1;
                }
            );

            array_walk($statuses,
                function ($status) {
                    Mage::getSingleton('avtoto/autopricing_process')->runProcess($status->getProcessCode());
                }
            );
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('No processes selected for mass action'));
        }

        $this->_redirect('*/*/status');
        return;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('avtoto');
    }
}
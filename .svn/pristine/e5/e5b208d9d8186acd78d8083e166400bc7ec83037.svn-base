<?php

class Testimonial_FlatCatalog_Adminhtml_Flatcatalog_ProductController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Product list page
     */
    public function indexAction()
    {
        $this->_title($this->__('Flat Catalog'))
             ->_title($this->__('Manage Products'));

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Update product(s) status action
     *
     */
    public function massStatusAction()
    {
        $productIds = (array)$this->getRequest()->getParam('flatproduct');
        $storeId    = (int)$this->getRequest()->getParam('store', 0);
        $status     = (int)$this->getRequest()->getParam('status');

        try {
            if (empty($productIds)){
                Mage::throwException($this->__('Please select products first.'));
            }
            Mage::getResourceSingleton('flatcatalog/product')
                ->updateAttributes($productIds, array('status' => $status), $storeId);

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($productIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the product(s) status.'));
        }

        $this->_redirect('*/*/', array('store'=> $storeId));
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/flatproducts');
    }
}

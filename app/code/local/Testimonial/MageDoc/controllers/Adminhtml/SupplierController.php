<?php

class Testimonial_MageDoc_Adminhtml_SupplierController extends Mage_Adminhtml_Controller_Action
{
    protected function _initSupplier()
    {
        $id  = $this->getRequest()->getParam('id');
        $supplier  = Mage::getModel('magedoc/supplier');
        if ($id) {
            $supplier->load($id);
            if (!$supplier->getId()){
                $tdSupplier = Mage::getModel('magedoc/tecdoc_supplier')
                    ->isPartialLoad(false)
                    ->load($id);

                $data = array(
                    'td_sup_id' => $tdSupplier->getSupId(),
                    'enabled'   => '0',
                    'title'     => $tdSupplier->getSupBrand(),
                    'logo'      => $tdSupplier->getSloId()
                        ? 'import/tecdoc/logos/'.$tdSupplier->getSloId() . '.png'
                        : null
                );

                $supplier->setData($data);
            }
        }

        Mage::register('supplier', $supplier);
        return $this;
    }

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('magedoc/suppliers')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Suppliers Manager'), Mage::helper('adminhtml')->__('Suppliers Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

    public function editAction()
    {
        $this->_initSupplier();
        $supplier = Mage::registry('supplier');
        $data = Mage::getSingleton('adminhtml/session')->getSupplierData(true);
        if (!empty($data)) {
            $supplier->addData($data);
        }

        $this->loadLayout();
        $this->_setActiveMenu('magedoc/suppliers');

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Suppliers Manager'), Mage::helper('adminhtml')->__('Suppliers Manager'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Suppliers News'), Mage::helper('adminhtml')->__('Suppliers News'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('magedoc/adminhtml_supplier_edit'))
            ->_addLeft($this->getLayout()->createBlock('magedoc/adminhtml_supplier_edit_tabs'));

        $this->renderLayout();

    }
 
	public function saveAction() {
        $data = $this->getRequest()->getPost();
		try {
            $this->_initSupplier();
            $supplier = Mage::registry('supplier');

            if (isset($_FILES['logo']['name']) and (file_exists($_FILES['logo']['tmp_name']))) {
                $_FILES['logo']['name'] = Mage::helper('magedoc')
                    ->transliterationString($_FILES['logo']['name']);
                $uploader = new Varien_File_Uploader('logo');
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png')); // or pdf or anything

                $uploader->setAllowRenameFiles(false);

                // setAllowRenameFiles(true) -> move your file in a folder the magento way
                // setAllowRenameFiles(true) -> move your file directly in the $path folder
                $uploader->setFilesDispersion(false);

                $path = Mage::getBaseDir('media') . DS . 'magedoc' . DS . 'logos' . DS;
                if ($result = $uploader->save($path, $_FILES['logo']['name'])){
                    $data['logo'] = $result['file'];
                }else{
                    $data['logo'] = false;
                }
            }else{
                unset($data['logo']);
            }

            $supplier->addData($data);
            $supplier->save();

			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Supplier was successfully saved'));
			Mage::getSingleton('adminhtml/session')->getSupplierData(true);

			if ($this->getRequest()->getParam('back')) {
				$this->_redirect('*/*/edit', array('id' => $supplier->getTdSupId()));
				return;
			}

            $this->_redirect('*/*/');
			return;
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('An error occurred while saving this supplier.'));
        }

        Mage::getSingleton('adminhtml/session')->setSupplierData($data);
        $this->_redirect('*/*/');
	}

    public function massEnabledAction()
    {
        $tdSupIds = $this->getRequest()->getParam('magedoc');
        if (!is_array($tdSupIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                $supplier = Mage::getModel('magedoc/supplier');
                $tdSupplier = Mage::getModel('magedoc/tecdoc_supplier')
                    ->isPartialLoad(false);
                foreach ($tdSupIds as $tdSupId) {
                    $supplier->setData(array());
                    $supplier->setOrigData();
                    if ($supplier->load($tdSupId)->getTdSupId()) {
                        $supplier->setEnabled($this->getRequest()->getParam('enabled'))
                            ->setIsMassupdate(true)
                            ->save();
                    } else {
                        $tdSupplier->load($tdSupId);
                        $data = array(
                            'td_sup_id' => $tdSupId,
                            'enabled' => $this->getRequest()->getParam('enabled'),
                            'title' => $tdSupplier->getSupBrand(),
                            'logo' => $tdSupplier->getSloId()
                                ? 'import/tecdoc/logos/' . $tdSupplier->getSloId() . '.png'
                                : null);
                        $supplier->setData($data);
                        $supplier->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($tdSupIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }

        }
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('magedoc/suppliers');
    }
}
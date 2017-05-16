<?php

class Testimonial_MageDoc_Adminhtml_ManufacturerController extends Mage_Adminhtml_Controller_Action
{
    protected function _initManufacturer()
    {
        $id  = $this->getRequest()->getParam('id');
        $manufacturer  = Mage::getModel('magedoc/manufacturer');
        if ($id) {
            $manufacturer->load($id);
            if (!$manufacturer->getId()){
                $tdManufacturer = Mage::getModel('magedoc/tecdoc_manufacturer')->load($id);

                $data = array(
                    'td_mfa_id' => $tdManufacturer->getMfaId(),
                    'enabled'   => '1',
                    'name'      => $tdManufacturer->getMfaBrand(),
                    'title'     => $tdManufacturer->getMfaBrand(),
                    'url_key'   => $tdManufacturer->getMfaBrand(),
                );
                $data = Mage::helper('magedoc')->getEntityDefaultValues('manufacturer', $data);
                $data['url_key'] = Mage::getSingleton('catalog/product_url')->formatUrlKey($data['url_key']);
                $manufacturer->setData($data);
            }
        }

        Mage::register('manufacturer', $manufacturer);
        return $this;
    }

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('magedoc/manufacturers')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Manufacturers'), Mage::helper('adminhtml')->__('Manage Manufacturers'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
        $this->_initManufacturer();
        $manufacturer = Mage::registry('manufacturer');
        $data = Mage::getSingleton('adminhtml/session')->getManufacturerData(true);
        if (!empty($data)) {
            $manufacturer->addData($data);
        }

        $this->loadLayout();
        $this->_setActiveMenu('magedoc/items');

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Manufacturers'), Mage::helper('adminhtml')->__('Manage Manufacturers'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('magedoc/adminhtml_manufacturer_edit'))
            ->_addLeft($this->getLayout()->createBlock('magedoc/adminhtml_manufacturer_edit_tabs'));

        $this->renderLayout();      
            
	}
 
	public function saveAction() {
        $data = $this->getRequest()->getPost();
        try {
            $this->_initManufacturer();
            $manufacturer = Mage::registry('manufacturer');
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

            $manufacturer->addData($data);
            $manufacturer->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Manufacturer was successfully saved'));
            Mage::getSingleton('adminhtml/session')->getManufacturerData(true);

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $manufacturer->getTdMfaId()));
                return;
            }

            $this->_redirect('*/*/');
            return;
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('An error occurred while saving this manufacturer.'));
            Mage::logException($e);
        }

        Mage::getSingleton('adminhtml/session')->setManufacturerData($data);
        $this->_redirect('*/*/');
	}
    public function massEnabledAction()
    {
        $tdMfaIds = $this->getRequest()->getParam('magedoc');        
        if(!is_array($tdMfaIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                $manufacturer = Mage::getModel('magedoc/manufacturer');
                $tdManufacturer = Mage::getModel('magedoc/tecdoc_manufacturer');
                foreach ($tdMfaIds as $tdMfaId) {
                    $manufacturer->setData(array());
                    $manufacturer->setOrigData();
                    if($manufacturer->load($tdMfaId)->getTdMfaId()){
                        $manufacturer->setEnabled($this->getRequest()->getParam('enabled'))
                            ->setIsMassupdate(true)
                            ->save();
                    } else {
                        $tdManufacturer->setData(array());
                        $tdManufacturer->load($tdMfaId);
                        $data = array(
                            'td_mfa_id' => $tdMfaId,
                            'enabled'   => $this->getRequest()->getParam('enabled'),
                            'name'      => $tdManufacturer->getMfaBrand(),
                            'title'     => $tdManufacturer->getMfaBrand(),
                            'url_key'   => Mage::getSingleton('catalog/product_url')->formatUrlKey($tdManufacturer->getMfaBrand()),
                            'logo'      => null,
                            'description' => null);
                        $manufacturer->setData($data);
                        $manufacturer->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($tdMfaIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('magedoc/manufacturers');
    }
}
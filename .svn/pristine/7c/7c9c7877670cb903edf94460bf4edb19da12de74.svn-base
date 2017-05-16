<?php

class Testimonial_MageDoc_Adminhtml_CriteriaController extends Mage_Adminhtml_Controller_Action
{
    protected function _initCriteria()
    {
        $id  = $this->getRequest()->getParam('id');
        $criteria = Mage::getModel('magedoc/criteria');
        if ($id) {
            $criteria->load($id);
            if (!$criteria->getId()){
                $tdCriteria = Mage::getModel('magedoc/tecdoc_criteria')->load($id);

                $data = array(
                    'td_mfa_id' => $tdCriteria->getMfaId(),
                    'enabled'   => '0',
                    'name'      => $tdCriteria->getMfaBrand(),
                    'title'     => $tdCriteria->getMfaBrand(),
                    'url_key'   => Mage::getSingleton('catalog/product_url')->formatUrlKey($tdCriteria->getMfaBrand()),
                );
                $criteria->setData($data);
            }
        }

        Mage::register('criteria', $criteria);
        return $this;
    }

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('magedoc/criteria')
			->_addBreadcrumb(Mage::helper('magedoc')->__('Manage Article Criteria'), Mage::helper('magedoc')->__('Manage Article Criteria'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
        $this->_initCriteria();
        $criteria = Mage::registry('criteria');
        $data = Mage::getSingleton('adminhtml/session')->getCriteriaData(true);
        if (!empty($data)) {
            $criteria->addData($data);
        }

        $this->loadLayout();
        $this->_setActiveMenu('magedoc/criteria');

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Criteria'), Mage::helper('adminhtml')->__('Manage Criteria'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('magedoc/adminhtml_criteria_edit'))
            ->_addLeft($this->getLayout()->createBlock('magedoc/adminhtml_criteria_edit_tabs'));

        $this->renderLayout();      
            
	}
 
	public function _saveAction() {
        $data = $this->getRequest()->getPost();
        try {
            $this->_initCriteria();
            $criteria = Mage::registry('criteria');
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

            $criteria->addData($data);
            $criteria->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Criteria was successfully saved'));
            Mage::getSingleton('adminhtml/session')->getCriteriaData(true);

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $criteria->getTdMfaId()));
                return;
            }

            $this->_redirect('*/*/');
            return;
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('An error occurred while saving this criteria.'));
            Mage::logException($e);
        }

        Mage::getSingleton('adminhtml/session')->setCriteriaData($data);
        $this->_redirect('*/*/');
	}

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('magedoc/criteria');
            $updatedRowsCount = 0;
            $updatedIds = array();
            foreach($data['criteria'] as $key => $row) {
                $model->setData(array());
                $model->setOrigData();
                $model->load($key);
                $this->setDefaultCriteriaValues($model, $key);

                $isEmpty = true;
                $defaultValues = array(
                    'enabled' => 0,
                    'is_import_enabled' => 0,
                    'attribute_code' => null
                );
                foreach ($defaultValues as $key => $value){
                    if ((is_int($value) && !isset($row[$key]))
                        || is_null($value) && empty($row[$key])){
                        $row[$key] = $value;
                    } else {
                        $isEmpty = false;
                    }
                }

                $model->addData($row);
                $data = $model->getData();
                $origData = $model->getOrigData();
                $updated = count(array_merge(array_diff_assoc($origData,$data),array_diff_assoc($data,$origData)));

                if(($model->getOrigData('td_cri_id') && $updated)
                    || (!$model->getOrigData('td_cri_id') && !$isEmpty)) {
                    $updatedRowsCount++;
                    $updatedIds[] = $key;
                    $model->save();
                }
            }

            if ($updatedRowsCount){
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('%s criteria updated successfully', $updatedRowsCount));
            } else {
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('magedoc')->__('No criteria have been changed'));
            }

            $this->_redirect('*/*/');
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function massEnabledAction()
    {
        $this->massUpdate( array('enabled' => $this->getRequest()->getParam('enabled')) );
    }

    public function massIsImportEnabledAction()
    {
        $this->massUpdate( array('is_import_enabled' => $this->getRequest()->getParam('is_import_enabled')) );
    }

    protected function massUpdate( $updateData )
    {
        $tdCdsIds = $this->getRequest()->getParam('magedoc');
        if(!is_array($tdCdsIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                $criteria = Mage::getModel('magedoc/criteria');
                foreach ($tdCdsIds as $tdCdsId) {
                    $criteria->setData(array());
                    $criteria->setOrigData();
                    if($criteria->load($tdCdsId)->getTdCdsId()){
                        foreach($updateData as $key=>$value) {
                            $criteria->setData($key, $value);
                        }
                        $criteria
                            ->setIsMassupdate(true)
                            ->save();
                    } else {
                        $this->setDefaultCriteriaValues( $criteria, $tdCdsId );

                        foreach($updateData as $key=>$value) {
                            $criteria->setData($key, $value);
                        }

                        $criteria->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($tdCdsIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function setDefaultCriteriaValues( $criteria, $id )
    {
        $tdModel = Mage::getModel('magedoc/tecdoc_criteria');
        $tdModel->isPartialLoad(false);
        $tdModel->setData(array());
        $tdModel->load($id);

        $data = array(
            'td_cri_id' => $id,
            'name'      => $tdModel->getCriDesText(),
            'default_name' => $tdModel->getCriDesTextEng()
            );

        if (!$criteria->getId()){
            $data = array_merge($data, array(
                'enabled'   => null,
                'is_import_enabled'   => null,
            ));
        }

        $criteria->addData($data);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('magedoc/criteria');
    }
}
<?php

class Testimonial_MageDoc_Adminhtml_ModelController extends Mage_Adminhtml_Controller_Action
{
    protected function _initModel()
    {
        $id  = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('magedoc/model');
        if ($id) {
            $model->load($id);
            if (!$model->getId()){
                $this->setDefaultValuesToModel($model, $id);
            }
        }

        Mage::register('model', $model);
        return $this;
    }

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('magedoc/models')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Models'), Mage::helper('adminhtml')->__('Manage Models'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
        $this->_initModel();
        $model = Mage::registry('model');
        $data = Mage::getSingleton('adminhtml/session')->getModelData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->loadLayout();
        $this->_setActiveMenu('magedoc/items');

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Models'), Mage::helper('adminhtml')->__('Manage Models'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('magedoc/adminhtml_model_edit'))
            ->_addLeft($this->getLayout()->createBlock('magedoc/adminhtml_model_edit_tabs'));

        $this->renderLayout();      
            
	}
 
	public function saveAction() {
        $data = $this->getRequest()->getPost();
        try {
            $this->_initModel();
            $model = Mage::registry('model');

            $model->addData($data);
            $model->save();
            $model->deleteTypeProduct();
            if(!empty($data['product']['id'])){
                $ids = $data['product']['id'];
                $model->setTypeProduct($ids);
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magedoc')->__('Model was successfully saved'));
            Mage::getSingleton('adminhtml/session')->getModelData(true);

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $model->getTdModId()));
                return;
            }

            $this->_redirect('*/*/');
            return;
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('An error occurred while saving this model.'));
            Mage::logException($e);
        }

        Mage::getSingleton('adminhtml/session')->setModelData($data);
        $this->_redirect('*/*/');
	}

    public function massEnabledAction()
    {
        $this->massUpdate( array('enabled' => $this->getRequest()->getParam('enabled')) );
    }

    public function massVisibleAction()
    {
        $this->massUpdate( array('visible' => $this->getRequest()->getParam('visible')) );
    }

    protected function massUpdate( $updateData )
    {
        $tdModIds = $this->getRequest()->getParam('magedoc');
        if(!is_array($tdModIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                $model = Mage::getModel('magedoc/model');
                foreach ($tdModIds as $tdModId) {
                    $model->setData(array());
                    $model->setOrigData();
                    if($model->load($tdModId)->getTdModId()){
                        foreach($updateData as $key=>$value) {
                            $model->setData($key, $value);
                        }
                        $model
                            ->setIsMassupdate(true)
                            ->save();
                    } else {
                        $this->setDefaultValuesToModel( $model, $tdModId );

                        foreach($updateData as $key=>$value) {
                            $model->setData($key, $value);
                        }

                        $model->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($tdModIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function setDefaultValuesToModel( $model, $id )
    {
        $tdModel = Mage::getModel('magedoc/tecdoc_model');
        $tdModel->isPartialLoad(false);
        $tdModel->setData(array());
        $tdModel->load($id);

        $data = array(
            'td_mod_id' => $id,
            'name'      => $tdModel->getModCdsText(),
            'enabled'   => null,
            'visible'   => null,
            'title'     => $tdModel->getMfaBrand() . ' ' . $tdModel->getModCdsText(),
            'url_key'   => Mage::getSingleton('catalog/product_url')->formatUrlKey($tdModel->getModCdsText()),
            'description' => null);

        $model->setData($data);
    }

    public function gridAction()
    {
        $this->_initModel();
        $this->getResponse()->setBody($this->getLayout()->createBlock('magedoc/adminhtml_model_edit_tab_product')->toHtml());
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('magedoc/models');
    }
}
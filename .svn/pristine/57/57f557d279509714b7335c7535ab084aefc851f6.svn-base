<?php

class Testimonial_MageDoc_ModelController extends Mage_Core_Controller_Front_Action
{

    public function viewAction()
    {
        try {
            if (!$model = $this->initModel()){
                $this->_forward('noRoute');
                return;
            }

            $this->loadLayout();
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            $this->_forward('noRoute');
        }
    }

    public function initModel()
    {
        $modelId = (int) $this->getRequest()->getParam('id');
        if (!$modelId) {
            return false;
        }
        $type = Mage::getModel('magedoc/tecdoc_model')
            ->isPartialLoad(false)
            ->load($modelId);
        if (!$type->getId()) {
            return false;
        }
        Mage::register('model', $type);
        return $type;
    }

}
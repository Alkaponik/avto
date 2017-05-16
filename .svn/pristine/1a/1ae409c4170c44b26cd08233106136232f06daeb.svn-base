<?php

class Testimonial_MageDoc_TypeController extends Mage_Core_Controller_Front_Action
{

    public function viewAction()
    {
        try {
            if (!$type = $this->initType()){
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

    public function initType()
    {
        $typeId = (int) $this->getRequest()->getParam('id');
        if (!$typeId) {
            return false;
        }
        $type = Mage::getModel('magedoc/tecdoc_type')
            ->isPartialLoad(false)
            ->load($typeId);
        if (!$type->getId()) {
            return false;
        }
        Mage::register('magedoc_type', $type);
        return $type;
    }

}
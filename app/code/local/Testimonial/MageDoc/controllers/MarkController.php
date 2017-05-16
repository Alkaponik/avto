<?php

class Testimonial_MageDoc_MarkController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        try {
            if (!$this->initMark()) {
                throw new Mage_Core_Exception(
                        $this->__('Mark not found'),
                        self::ERR_NO_MARK_LOADED);
            }
            $this->loadLayout();
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == self::ERR_NO_MARK_LOADED) {
                if (isset($_GET['store']) && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    $this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
    }

    public function initMark()
    {
        $manufacturerId = (int) $this->getRequest()->getParam('id');
        if (!$manufacturerId) {
            return false;
        }
        $manufacturer = Mage::getModel('magedoc/manufacturer')
                        ->load($manufacturerId);
        if (!$manufacturer->getId()) {
            return false;
        }
        Mage::register('manufacturer', $manufacturer);
        return $manufacturer;
    }

}
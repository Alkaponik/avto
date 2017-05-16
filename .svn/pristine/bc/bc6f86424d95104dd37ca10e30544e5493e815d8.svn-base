<?php

class Testimonial_MageDoc_MakeController extends Mage_Core_Controller_Front_Action
{
    const ERR_NO_MAKE_LOADED = 'no_make_found';

    public function viewAction()
    {
        $this->_forward('index');
    }

    public function indexAction()
    {
        try {
            if (!$this->initMake()) {
                throw new Mage_Core_Exception(
                        $this->__('Make not found'),
                        self::ERR_NO_MAKE_LOADED);
            }
            $this->loadLayout();
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == self::ERR_NO_MAKE_LOADED) {
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

    public function initMake()
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
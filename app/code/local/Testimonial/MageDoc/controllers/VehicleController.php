<?php

class Testimonial_MageDoc_VehicleController extends Mage_Core_Controller_Front_Action
{
    /**
     * @todo remove core/session usage
     */

    public function requestAction()
    {
        if ($item = $this->getRequest()->getPost('item')) {
            if ($value = $this->getRequest()->getPost('value')) {
                switch ($item) {
                    case 'manufacturer':
                        Mage::getSingleton('core/session')->setManufacturerId($value);
                        $source = Mage::getModel('magedoc/source_date')
                            ->setSortOrder('desc')
                            ->getExtendedOptionArray('sort_order');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
                        break;
                    case 'production_start_year':
                    case 'date':
                        $manufacturerId = $this->getRequest()->getPost('manufacturer_id', false);
                        if (!$manufacturerId) {
                            $manufacturerId = Mage::getSingleton('core/session')->getManufacturerId();
                        }

                        $source = Mage::getModel('magedoc/source_model')
                            ->setYearStart($value)
                            ->setManufacturerId($manufacturerId)
                            ->getExtendedOptionArray('sort_order');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
                        break;
                    case 'model':
                        $source = Mage::getModel('magedoc/source_type')
                            ->setModelId($value)
                            ->getExtendedOptionArray('sort_order');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
                        break;
                    default:
                        break;
                }
            }
        }
    }
      
    public function indexAction()
    {
       $customerSession = Mage::getSingleton('customer/session');
       if(!$customerSession->isLoggedIn()){
            $this->_redirect('customer/account/login');
            return;
        }
        $customer = $customerSession->getCustomer();
        Mage::register('customer', $customer);

        $this->loadLayout();        
        $this->renderLayout();
    }
    
    
    public function saveAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        try {
            if($vehicles = $this->getRequest()->getPost('vehicle')){

                foreach ($customer->getVehiclesCollection() as $vehicle) {
                    if (!isset($vehicles[$vehicle->getId()])) {
                        $vehicle->delete();
                    }
                }

                foreach($vehicles as $vehicle){
                    $customer->addVehicle($vehicle);
                }

                $customer
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->save();
            }            
        }
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your vehicle.'));
        }
        
        $this->_redirect('customer/account/index');
    }

    public function makesAction()
    {
        $year = $this->getRequest()->getParam('year');
        $source = Mage::getModel('magedoc/source_manufacturer')
            ->setYearFilter($year)
            ->setEnabledFilter(true)
            ->getExtendedOptionArray('sort_order');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
    }

    public function modelsAction()
    {
        $year = $this->getRequest()->getParam('year');
        $manufacturerId = $this->getRequest()->getParam('manufacturer');
        $source = Mage::getModel('magedoc/source_model')
            ->setYearStart($year)
            ->setManufacturerId($manufacturerId)
            ->setIsGrouped()
            ->getExtendedOptionArray('sort_order');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
    }

    public function bodiesAction()
    {
        $year = $this->getRequest()->getParam('year');
        $manufacturerId = $this->getRequest()->getParam('manufacturer');
        $modelIds = $this->getRequest()->getParam('models');
        $source = Mage::getModel('magedoc/source_type_body')
            ->setModelIds($modelIds)
            ->setProductionYear($year)
            ->getExtendedOptionArray('sort_order');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
    }

    public function enginesAction()
    {
        $typeIds = $this->getRequest()->getParam('type_ids');
        $source = Mage::getModel('magedoc/source_type_engine')
            ->setTypeIds($typeIds)
            ->getAllOptions(false);
            //->getExtendedOptionArray('sort_order');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
    }

    public function typesAction()
    {
        $year = $this->getRequest()->getParam('year');
        $manufacturerId = $this->getRequest()->getParam('manufacturer');
        $modelIds = $this->getRequest()->getParam('models');
        $typeIds = $this->getRequest()->getParam('type_ids');
        $source = Mage::getModel('magedoc/source_type')
            ->setTypeIds($typeIds)
            ->getModelTypes();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($source));
    }

    public function typeAction()
    {
        $typeId = $this->getRequest()->getParam('type_id');
        $type = Mage::getModel('magedoc/tecdoc_type')->load($typeId);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($type->getData()));
    }
}
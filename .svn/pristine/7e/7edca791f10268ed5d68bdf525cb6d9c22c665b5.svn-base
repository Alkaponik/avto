<?php
class Testimonial_MageDoc_Adminhtml_Product_MapController extends Mage_Adminhtml_Controller_action
{
    protected function _initAction()
    {
        $this->loadLayout()
			->_setActiveMenu('magedoc/product_map')
			->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Products Map'),
                Mage::helper('adminhtml')->__('Products Map')
            );

		return $this;
	}
 
	public function indexAction()
    {
        $this->_initAction()
			->renderLayout();
	}

	public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $updatedRowsCount = 0;
            $productIds = array();
            $directory = $this->getRequest()->getParam( 'directory' ) ? : Mage::helper('magedoc')->getDefaultDirectoryCode();
            $directory = Mage::getSingleton('magedoc/directory')
                ->getDirectory($directory );
            try {
                if (!isset($data['product_map']) || !is_array($data['product_map'])){
                    Mage::throwException($this->__('No items were saved.'));
                }
                switch ($directory->getCode()){
                    case 'tecdoc':
                        $productIdFieldName = 'td_art_id';
                        break;
                    case 'catalog':
                        $productIdFieldName = 'product_id';
                        break;
                    default:
                        $productIdFieldName = null;
                }
                foreach($data['product_map'] as $key => $row) {
                    if (isset($row['product_id'])){
                        $productIds[] = array(
                            'data_id'    => $key,
                            'directory_entity_id' => !empty($row['product_id'])
                                ? $row['product_id']
                                : null
                        );
                    }

                }
                /* @var $resource Testimonial_MageDoc_Model_Mysql4_Import_Retailer_Data */
                $resource = Mage::getResourceSingleton('magedoc/import_retailer_data');
                $connection = $resource->getReadConnection();

                $tableName = $resource->getTable('magedoc/directory_offer_link');
                $updatedRowsCount = $connection->insertOnDuplicate($tableName, $productIds, array('directory_entity_id'));
                if ($productIdFieldName
                    && $connection->tableColumnExists($resource->getMainTable(), $productIdFieldName)){
                    foreach ($productIds as $key => $productId){
                        $productIds[$key][$productIdFieldName] = $productId['directory_entity_id'];
                        unset($productIds[$key]['directory_entity_id']);
                    }
                    $updatedRowsCount = max($updatedRowsCount,
                        $resource->massUpdate($productIds, array($productIdFieldName)));
                }

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s offer(s) were updated successfully', $updatedRowsCount));
            } catch(Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch(Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::logException($e);
            }
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/adminhtml_retailer/edit',
                    array(
                         'id' => $this->getRequest()->getParam('retailer_id'),
                         '_current' => true
                    ));
                return;
            }

            $this->_redirect('*/*/',array('_current' => true));
			return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}

    public function suggestAction()
    {
        try {
            $this->_initAction()
                ->renderLayout();
        } catch (Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magedoc')->__('Unable to suggest products'));
            $this->_redirect('*/*/');
        }
    }

    public function supplierlistAction()
    {
        $directoryCode = $this->getRequest()->getParam('directory');
        $directory = Mage::getSingleton('magedoc/directory')->getDirectory($directoryCode);
        $html = '';
        if($directory) {
            $suppliers  = $directory->getSupplierOptions();
            $suppliers[0] = Mage::helper('magedoc')->__('--Not linked to directory--');
            $html = json_encode($suppliers);
        }

        $this->getResponse()->setBody($html);
    }

    public function productlistAction()
    {
        $directoryCode = $this->getRequest()->getParam('directory');
        $manufacturer = $this->getRequest()->getParam('manufacturer');
        $directory = Mage::getSingleton('magedoc/directory')->getDirectory($directoryCode);
        $html = '';
        $manufacturer = 2594;
        if($directory) {
            $products[0] = Mage::helper('magedoc')->__('--Not linked to directory--');
            $html = json_encode(
                array_merge($products, $this->getLayout()->createBlock('magedoc/adminhtml_product_map_grid')
                    ->getManufacturerProductOptions($manufacturer))
            );
        }

        $this->getResponse()->setBody($html);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('magedoc/product_map');
    }
}
<?php
class Testimonial_MageDoc_Adminhtml_Supplier_MapController extends Mage_Adminhtml_Controller_action
{
    protected function _initAction()
    {
        $this->loadLayout()
			->_setActiveMenu('magedoc/supplier_map')
			->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Suppliers Map'),
                Mage::helper('adminhtml')->__('Suppliers Map')
            );

		return $this;
	}
 
	public function indexAction()
    {
        $this->_initAction()
			->renderLayout();
	}

 	public function editAction()
    {
        $this->_initRetailer();
        $this->loadLayout();
        $this->_setActiveMenu('magedoc/retailers');

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Retailers Manager'), Mage::helper('adminhtml')->__('Retailers Manager'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit'))
            ->_addLeft($this->getLayout()->createBlock('magedoc/adminhtml_retailer_edit_tabs'));

        $this->renderLayout();
    }

    public function newAction()
    {
       $this->_forward('edit');
    }

	public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('magedoc/supplier_map');
            $updatedRowsCount = 0;
            $updatedIds = array();
            $retailerIds = array();
            $newManufacturers = array();
            $textValues = isset($data['text_mapItem']) ? $data['text_mapItem'] : array();
            $directory = $this->getRequest()->getParam( 'directory' ) ? : Mage::helper('magedoc')->getDefaultDirectoryCode();
            $directory = Mage::getSingleton('magedoc/directory')
                ->getDirectory($directory );
            try {
                foreach($data['mapItem'] as $key => $row) {
                    $newManufacturerAddedToDirectory = !isset($row['supplier_id']) && !empty( $textValues[$key]['supplier_id']);

                    $model->load($key);
                    if (!isset($row['use_crosses'])){
                        $row['use_crosses'] = 0;
                    }
                    if (!isset($row['discount_percent'])
                        || $row['discount_percent'] === ''){
                        $row['discount_percent'] = null;
                    }
                    if (empty($row['supplier_id'])){
                        $row['supplier_id'] = null;
                    }
                    $model->addData($row);
                    $data = $model->getData();
                    $origData = $model->getOrigData();

                    $updated =
                        count(array_merge(array_diff_assoc($origData,$data),array_diff_assoc($data,$origData))) ||
                            $newManufacturerAddedToDirectory;

                    if($updated) {
                        $updatedRowsCount++;
                        $updatedIds[] = $key;

                        $retailerIds[$model->getRetailerId()] = $model->getRetailerId();

                        if( $newManufacturerAddedToDirectory ) {
                            $supplierId = $directory->addSupplier( $textValues[$key]['supplier_id'] );
                            $newManufacturers[] =  $textValues[$key]['supplier_id'];
                            $model->setSupplierId($supplierId);
                        }
                        $model->save();
                    }
                }
                $directory->updateSupplierIdInSupplierMap( null, $newManufacturers);
                $this->_updatePreviewTable($retailerIds, $updatedIds);
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

    protected function _updatePreviewTable( $retailerIds, $updatedIds )
    {
        if(empty($retailerIds)) {
            return;
        }

        $baseRecordsLinked = 0;

        $retailers = Mage::getModel('magedoc/retailer')
                ->getCollection()
                ->addFieldToFilter('retailer_id', $retailerIds);

        while($retailer = $retailers->fetchItem()) {
            if(!$retailer->hasActiveSession()) {
                continue;
            }
            /** @var Testimonial_MageDoc_Model_Retailer_Data_Import $import */
            $import = Mage::getModel('magedoc/retailer_data_import')->setRetailer($retailer);
            $import->updatePreview($updatedIds);

            $importSession = $import->getSession();
            $message = Mage::helper('magedoc')->__(
                '%s retailer price preview table is updated.
                 %d records are linked to supplier.
                 %d suppliers are linked to directory. %d suppliers is not found in the map.
                 %d price records are linked to directory.'
            );

            Mage::getSingleton('adminhtml/session')
                ->addSuccess(
                    sprintf(
                        $message,
                        $retailer->getName(),
                        $importSession->getRecordsWithOldBrands(),
                        $importSession->getOldBrands(),
                        $importSession->getNewBrands(),
                        $importSession->getRecordsLinkedToDirectory()
                    )
                );
        }

        Mage::getSingleton('adminhtml/session')->addSuccess(
            sprintf(Mage::helper('magedoc')
                    ->__("%d rows in supplier map were updated. %d records in the base table were updated."),
                count($updatedIds), $baseRecordsLinked
            )
        );
    }

    public function suggestAction()
    {
        $this->_initAction()
            ->renderLayout();
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

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('magedoc/supplier_map');
    }
}
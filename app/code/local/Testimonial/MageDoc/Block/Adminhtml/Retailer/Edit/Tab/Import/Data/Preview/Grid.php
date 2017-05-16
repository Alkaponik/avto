<?php
class Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Import_Data_Preview_Grid
    extends Testimonial_MageDoc_Block_Adminhtml_Retailer_Import_Grid_Abstract
{
    protected $_collectionModelName = 'magedoc/retailer_data_import_preview_collection';

    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->setId('retailer_import_preview');
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('tmpPrice');
    }

    protected function __prepareCollection()
    {
        parent::__prepareCollection();

        $retailerId = Mage::registry('retailer')->getRetailerId();
        $this->getCollection()
            ->addFieldToFilter('main_table.retailer_id', $retailerId);

        return $this;
    }

    protected function _joinPreviewTableToCollection($collection)
    {
        $resource = $collection->getResource();

        /** @var Testimonial_MageDoc_Model_Retailer $retailer */

        $joinConditions = "preview.code_raw = main_table.code_raw
                    AND preview.retailer_id = main_table.retailer_id
                    AND preview.manufacturer = main_table.manufacturer";

        $retailer = Mage::registry('retailer');
        if( false && $retailer->hasActiveSession() ) {
            /** @var Testimonial_MageDoc_Model_Retailer_Data_Import_Adapter_Config $importAdapterConfig */
            $importAdapterConfig = $retailer
                ->getActiveSession()
                ->getLastSource()
                ->getConfig();

            if($importAdapterConfig->isUpdateConfig()) {
                $fields = $importAdapterConfig->getUpdateKeyFields();
                $fields []= 'retailer_id';
                $joinConditions = array();
                foreach($fields as $field) {
                    $joinConditions[] = "preview.$field = main_table.$field";
                }

                $joinConditions = implode(' AND ', $joinConditions );
            }
        }

        $collection->getSelect()
            ->joinLeft(
                array('preview' => $resource->getTable('magedoc/import_retailer_data_preview')),
                $joinConditions,
                $this->_getPreviewTableColumns()
            )
            ->joinLeft(
                array('directory_offer_link' => $resource->getTable('magedoc/directory_offer_link_preview')),
                "preview.data_id = directory_offer_link.data_id AND directory_offer_link.directory_code = '{$this->_getCurrentDirectoryId()}'",
                array('directory_entity_id','directory_code','supplier_id')
            );

        if( $session = $retailer->getLastSession() )
        {
            $sources = $session->getSourcesForPreview($this->_getCurrentDirectoryId());
            $sourceIds = array();
            foreach($sources as $source) {
                $sourceIds[] = $source->getId();
            }

            if( is_array($sourceIds) && !empty($sourceIds) ) {
                $collection->getSelect()
                    ->where('main_table.source_id IN ('. implode(',' , $sourceIds) .')');
            } else {
                $collection->getSelect()
                    ->where('0');
            }
        }
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('retailer_id');

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getCurrentUrl( array('remove_switcher' => 1) );
    }

}
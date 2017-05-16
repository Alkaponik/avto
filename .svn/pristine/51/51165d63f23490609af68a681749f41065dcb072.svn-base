<?php
class Testimonial_MageDoc_Block_Adminhtml_Retailer_Edit_Tab_Price_Upload_Preview_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    const PREVIEW_ROWS_COUNT = 50;

    public function __construct()
    {
        parent::__construct();

        $this->setId('previewId');
        $this->setSaveParametersInSession(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    protected function _prepareCollection()
    {
        /** @var Testimonial_MageDoc_Model_Retailer_Data_Import _collection */
        $this->_collection = Mage::getModel('magedoc/retailer_data_import')
            ->setRetailer(Mage::registry('retailer'))
            ->getFilePreviewCollection( static::PREVIEW_ROWS_COUNT, true );

        parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn( 'item_index',
            array(
                'header' => '#',
                'width' => '80px',
                'index' => 'item_index',
            )
        );
        foreach(range(1, $this->_getMaxRowLength()+1) as $v) {
            $headerMap
                = Mage::registry('retailer')->getActiveSession()->getLastSource()->getConfig()->getHeaderMap();

            $title = isset($headerMap[$v]) ? $v . ' (' . $headerMap[$v] . ') ' : $v;
            $this->addColumn( $v,
                array(
                    'header' => $title,
                    'width' => '80px',
                    'index' =>  $v - 1 ,
                )
            );
        }
        return parent::_prepareColumns();
    }

    protected function _getMaxRowLength()
    {
        $maxRowSize = 0;
        foreach( $this->getCollection() as $item) {
            for($i = count($item->getData()); $i > 0; $i--) {
                if( $item[$i] != '') {
                    if($i > $maxRowSize) {
                        $maxRowSize = $i;
                    }
                }
            }
        }
        return $maxRowSize;
    }

    protected function _prepareGrid()
    {
        $this->_prepareCollection();
        $this->_prepareColumns();
        $this->_prepareMassactionBlock();
        return $this;
    }
}
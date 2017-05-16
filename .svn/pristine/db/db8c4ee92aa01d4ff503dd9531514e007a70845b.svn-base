<?php
class Testimonial_Avtoto_Block_Adminhtml_Autopricing_Process_Status_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_statusModel;

    public function __construct()
    {
        parent::__construct();
        $this->_statusModel = Mage::getSingleton('avtoto/autopricing_process_status');
        $this->_filterVisibility = false;
        $this->_pagerVisibility  = false;
    }

    protected function _prepareCollection()
    {
        /** @var Testimonial_Avtoto_Model_Resource_Autopricing_Process_Status_Collection $collection */
        $collection = Mage::getResourceModel('avtoto/autopricing_process_status_collection');

        $processArray = Mage::getSingleton('avtoto/autopricing_process')->getProcesses();

        foreach ($collection as $item)
        {
            $code = $item->getProcessCode();
            if(isset($processArray[$code])) {
                $data = array_merge(
                    (array)$processArray[$code],
                    array(
                         'status' => $item->getStatus(),
                    )
                );
                $item->addData($data);
                unset($processArray[$code]);
            } else {
                $collection->removeItemByKey($item->getId());
                $item->delete();
            }
        }

        foreach($processArray as $k => $v) {
            $v = array_merge(
                (array)$v,
                array(
                     'process_code' => $k,
                     'status' => 'require_reindex',
                )
            );
            $statusModel = Mage::getModel('avtoto/autopricing_process_status')->addData($v)->save();
            $collection->addItem($statusModel);
        }


        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $items = $this->_collection->getItems();
        uasort($items,
            function($a, $b){
                if ($a['sort_order'] == $b['sort_order']) {
                    return 0;
                }
                return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
            });

        $this->_collection->setItems( $items );
    }


    protected function _prepareColumns()
    {
        $this->addColumn('process_code',
            array(
                 'header'    => Mage::helper('index')->__('Process'),
                 'align'     => 'left',
                 'index'     => 'process_code',
                 'sortable'  => false,
            )
        );
        $this->addColumn('title',
            array(
                'header'    => Mage::helper('index')->__('Process'),
                'align'     => 'left',
                'index'     => 'title',
                'sortable'  => false,
            )
        );

        $this->addColumn('status',
            array(
                'header'    => Mage::helper('index')->__('Status'),
                'width'     => '120',
                'align'     => 'left',
                'index'     => 'status',
                'frame_callback' => array($this, 'decorateStatus'),
                'type'      => 'options',
                'options'   => $this->_statusModel->getStatusesOptions(),
            )
        );

        $this->addColumn('ended_at',
            array(
                'header'    => Mage::helper('index')->__('Updated At'),
                'type'      => 'datetime',
                'width'     => '180',
                'align'     => 'left',
                'index'     => 'ended_at',
                'frame_callback' => array($this, 'decorateDate')
            )
        );

        $this->addColumn('action',
            array(
                 'header'    =>  Mage::helper('index')->__('Action'),
                 'width'     => '100',
                 'type'      => 'action',
                 'getter'    => 'getProcessCode',
                 'actions'   => array(
                     array(
                         'caption'   => Mage::helper('index')->__('Reindex Data'),
                         'url'       => array('base'=> '*/*/reindexProcess'),
                         'field'     => 'process_code',
                     ),
                 ),
                 'filter'    => false,
                 'sortable'  => false,
                 'is_system' => true,
                 'frame_callback' =>  array($this, 'getActionProcessCode'),
            )
        );

        return parent::_prepareColumns();
    }

    function getActionProcessCode($value, $row, $column, $isExport)
    {
        if($row->getProcessCode() == 'retailer_price_updated') {
            return '';
        }
        return $value;
    }

    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getStatus()) {
            case Testimonial_Avtoto_Model_Autopricing_Process_Status::STATUS_PENDING :
                $class = 'grid-severity-notice';
                break;
            case Testimonial_Avtoto_Model_Autopricing_Process_Status::STATUS_RUNNING :
                $class = 'grid-severity-minor';
                break;
            case Testimonial_Avtoto_Model_Autopricing_Process_Status::STATUS_REQUIRE_REINDEX :
            case Testimonial_Avtoto_Model_Autopricing_Process_Status::STATUS_FAILED :
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }


    public function decorateDate($value, $row, $column, $isExport)
    {
        if(!$value) {
            return $this->__('Never');
        }
        return $value;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('status_id');

        $this->getMassactionBlock()->addItem('reindex',
            array(
                'label'    => Mage::helper('index')->__('Reindex Data'),
                'url'      => $this->getUrl('*/*/massReindexProcess'),
                'selected' => true,

            )
        );

        return $this;
    }

}
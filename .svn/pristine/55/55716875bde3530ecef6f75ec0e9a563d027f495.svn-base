<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml newsletter subscribers grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Testimonial_CallBackRequest_Block_Adminhtml_Request_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('contactGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('request_id');
        $this->setDefaultDir('DESC');
        $this->_defaultFilter['status'] = Testimonial_CallBackRequest_Model_Request::STATUS_PENDING;
        $this->setSaveParametersInSession(true);

        $this->setColumnRenderers(
            array(
                'action'    => 'magedoc_system/adminhtml_widget_grid_column_renderer_action'));
    }
    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('callbackrequest/request_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _prepareColumns()
    {
        $items = Mage::getResourceModel('admin/user_collection');
        $managers = array();
        foreach($items as $item){
            $managers[$item->getUserId()] = $item->getName();
        }

        $this->addColumn('request_id',
            array(
                'header' => Mage::helper('callbackrequest')->__('ID'),
                'width' => '30px',
                'index' => 'request_id'
            ));
        $this->addColumn('name',
            array(
                'header' => Mage::helper('callbackrequest')->__('Name'),
                'type'  => 'text',
                'index' => 'name'
            ));
        $this->addColumn('comment',
            array(
                'header'    => Mage::helper('callbackrequest')->__('Comment'),
                'index'     => 'comment',
                'format'    => '$comment',
                'getter'    => array($this, 'getComment')
        ));
        $this->addColumn('telephone',
            array(
                'header' => Mage::helper('callbackrequest')->__('Telephone'),
                'index' => 'telephone'
        ));

        $this->addColumn('remote_addr',
            array(
                'header'    => Mage::helper('callbackrequest')->__('Customer IP'),
                'index'     => 'remote_addr',
                'getter'    => array($this, 'long2ip')
            ));

        $this->addColumn('status',
            array(
                'header' => Mage::helper('callbackrequest')->__('Status'),
                'index' => 'status',
                'type' => 'options',
                'options'   => array(
                    Testimonial_CallBackRequest_Model_Request::STATUS_PENDING => Mage::helper('callbackrequest')->__('Pending'),
                    Testimonial_CallBackRequest_Model_Request::STATUS_PROCESSED => Mage::helper('callbackrequest')->__('Processed')
                ))
        );

        $this->addColumn('manager',
            array(
                'header'        => Mage::helper('callbackrequest')->__('Manager'),
                'index'         => 'manager_name',
                'filter_index'  => 'manager_id',
                'type'          => 'options',
                'options'       => $managers,
                'show_missing_option_values'    => true
            )
        );

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('callbackrequest')->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('callbackrequest')->__('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('callbackrequest')->__('Action'),
            'index'     => 'request_id',
            'type'      => 'action',
            'getter'    => array($this, 'canChangeStatus'),
            'actions'   => array(
                array(
                    'caption' => Mage::helper('callbackrequest')->__('Processed'),
                    'url'     => array(
                        'base'=>'*/callbackrequest/status',
                        'params' => array(
                            'id' => '{{@request_id}}',
                            'token' => '{{@token}}'
                        )
                    ),
                )
            ),
        ));

        return parent::_prepareColumns();
    }

    /**
     * Convert OptionsValue array to Options array
     *
     * @param array $optionsArray
     * @return array
     */
    protected function _getOptions($optionsArray)
    {
        $options = array();
        foreach ($optionsArray as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    public function long2ip($row)
    {
        return long2ip($row->getRemoteAddr());
    }

    public function getComment($row)
    {
        $comment = $row->getComment();

        if (!$row->getCommentAppended()){
            $comment .= $row->getCartContent();
            $row->setCommentAppended(true);
            $row->setComment($comment);
        }

        return $comment;
    }

    public function getRowUrl($item)
    {
        $this->getComment($item);
        return parent::getRowUrl($item);
    }

    public function canChangeStatus($row)
    {
        return $row->getStatus() == Testimonial_CallBackRequest_Model_Request::STATUS_PENDING
            ? true
            : null;
    }
}

<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_Create_Customer_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Customer_Grid
{

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            //->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_regione', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
            ->joinField('store_name', 'core/store', 'name', 'store_id=store_id', null, 'left');

        $telephoneAttribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'telephone');
        $faxAttribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'fax');
        $filter   = $this->getParam($this->getVarNameFilter(), null);
        if (is_string($filter)) {
            $filter = $this->helper('adminhtml')->prepareFilterString($filter);
        }
        if (is_array($filter) && !empty($filter['Telephone'])){
            $collection->joinTable(array('customer_address' => 'customer/address_entity'), 'parent_id=entity_id', array('address_id' => 'entity_id'))
                ->joinField('billing_telephone', $telephoneAttribute->getBackend()->getTable(), 'value', 'entity_id=address_id', array('attribute_id' => array('in' => array($telephoneAttribute->getAttributeId(), $faxAttribute->getAttributeId()))), 'left');
            $collection->groupByAttribute('entity_id');
        } else{
            $collection->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left');
        }

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumnAfter('billing_city', array(
            'header' => Mage::helper('magedoc')->__('City'),
            'index' => 'billing_city',
        ), 'billing_regione');

        $this->addColumnAfter('manager', array(
            'header' => Mage::helper('magedoc')->__('Manager'),
            'index' => 'manager_id',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_orderManager')->getOptionArray(),
        ), 'store_name');

        return parent::_prepareColumns();
    }

    /**
     * Deprecated since 1.1.7
     */
    public function getRowId($row)
    {
        return $row->getId();
    }

    public function getRowUrl($row)
    {
        return $row->getId();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/loadBlock', array('block'=>'customer_grid'));
    }

}

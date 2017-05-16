<?php

class Testimonial_MageDoc_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
    protected function _prepareCollection()
    {
        $telephoneAttribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'telephone');
        $faxAttribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'fax');
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->addAttributeToSelect('gender')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');

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
        if (!Mage::app()->isSingleStoreMode()) {
            $after = 'website_id';
        }else{
            $after = 'customer_since';
        }

        $genderOptions = array();
        foreach (Mage::getResourceSingleton('customer/customer')
                ->getAttribute('gender')
                ->getSource()
                ->getAllOptions(false) as $option){
            $genderOptions[$option['value']] = $option['label'];
        }

        $this->addColumnAfter('gender', array(
            'header' => Mage::helper('magedoc')->__('Gender'),
            'index' => 'gender',
            'type'  => 'options',
            'options' => $genderOptions,
        ), 'name');

        $this->addColumnAfter('billing_city', array(
            'header' => Mage::helper('magedoc')->__('City'),
            'index' => 'billing_city',
        ), 'billing_region');

        $this->addColumnAfter('manager', array(
            'header' => Mage::helper('magedoc')->__('Manager'),
            'index' => 'manager_id',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('magedoc/source_orderManager')->getOptionArray(),
        ), $after);

        parent::_prepareColumns();

        return $this;
    }

    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $this->getMassactionBlock()->addItem('merge', array(
            'label'    => Mage::helper('customer')->__('Merge'),
            'url'      => $this->getUrl('*/crm_customer/massMerge'),
            'confirm'  => Mage::helper('customer')->__('Are you sure want to merge this customers?')
        ));
        return $this;
    }

}

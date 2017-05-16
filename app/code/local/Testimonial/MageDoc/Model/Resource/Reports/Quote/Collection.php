<?php

class Testimonial_MageDoc_Model_Resource_Reports_Quote_Collection
    extends Mage_Reports_Model_Resource_Quote_Collection
{
    /**
     * Prepare for abandoned report
     *
     * @param array $storeIds
     * @param string $filter
     * @return Mage_Reports_Model_Resource_Quote_Collection
     */
    public function prepareForAbandonedReport($storeIds, $filter = null)
    {
        $this->addFieldToFilter('items_count', array('neq' => '0'))
            ->addFieldToFilter('reserved_order_id', array('null' => true))
            ->addSubtotal($storeIds, $filter)
            ->addCustomerData($filter)
            ->setOrder('updated_at');
        if (is_array($storeIds) && !empty($storeIds)) {
            $this->addFieldToFilter('store_id', array('in' => $storeIds));
        }


        return $this;
    }

    public function addCustomerData($filter = null)
    {
        $customerEntity         = Mage::getResourceSingleton('customer/customer');
        $attrFirstname          = $customerEntity->getAttribute('firstname');
        $attrFirstnameId        = (int) $attrFirstname->getAttributeId();
        $attrFirstnameTableName = $attrFirstname->getBackend()->getTable();

        $attrLastname           = $customerEntity->getAttribute('lastname');
        $attrLastnameId         = (int) $attrLastname->getAttributeId();
        $attrLastnameTableName  = $attrLastname->getBackend()->getTable();

        $attrEmail       = $customerEntity->getAttribute('email');
        $attrEmailTableName = $attrEmail->getBackend()->getTable();

        $adapter = $this->getSelect()->getAdapter();
        $customerName = 'IFNULL('.$adapter->getConcatSql(array('cust_fname.value', 'IFNULL(cust_lname.value, "")'), ' ')
            . ',' .$adapter->getConcatSql(array('main_table.customer_firstname', 'IFNULL(main_table.customer_lastname, "")'), ' ')
            . ')';
        $this->getSelect()
            ->joinLeft(
            array('cust_email' => $attrEmailTableName),
            'cust_email.entity_id = main_table.customer_id',
            array(
                'email' => 'cust_email.email',
                'manager_id' => 'cust_email.manager_id')
        )
            ->joinLeft(
            array('cust_fname' => $attrFirstnameTableName),
            implode(' AND ', array(
                'cust_fname.entity_id = main_table.customer_id',
                $adapter->quoteInto('cust_fname.attribute_id = ?', (int)$attrFirstnameId),
            )),
            array('firstname' => 'cust_fname.value')
        )
            ->joinLeft(
            array('cust_lname' => $attrLastnameTableName),
            implode(' AND ', array(
                'cust_lname.entity_id = main_table.customer_id',
                $adapter->quoteInto('cust_lname.attribute_id = ?', (int)$attrLastnameId)
            )),
            array(
                'lastname'      => 'cust_lname.value',
                'customer_name' => $customerName
            )
        )
            ->joinLeft(
            array('billing_address' => $this->getTable('sales/quote_address')),
            'billing_address.quote_id = main_table.entity_id AND billing_address.address_type = "billing"',
            array(
                'telephone' => 'billing_address.telephone')
        );

        $this->_joinedFields['customer_name'] = $customerName;
        $this->_joinedFields['email']         = 'cust_email.email';
        $this->_joinedFields['manager_id']    = 'cust_email.manager_id';
        $this->_joinedFields['telephone']     = 'billing_address.telephone';

        $this->addFilterToMap('customer_name', '$customerName');
        $this->addFilterToMap('email', 'cust_email.email');
        $this->addFilterToMap('manager_id', 'cust_email.manager_id');
        $this->addFilterToMap('telephone', 'billing_address.telephone');

        if ($filter) {
            if (isset($filter['customer_name'])) {
                $likeExpr = '%' . $filter['customer_name'] . '%';
                $this->getSelect()->where($this->_joinedFields['customer_name'] . ' LIKE ?', $likeExpr);
            }
            if (isset($filter['email'])) {
                $likeExpr = '%' . $filter['email'] . '%';
                $this->getSelect()->where($this->_joinedFields['email'] . ' LIKE ?', $likeExpr);
            }
        }

        return $this;
    }
}
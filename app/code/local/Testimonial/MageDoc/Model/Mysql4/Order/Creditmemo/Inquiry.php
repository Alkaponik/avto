<?php

class  Testimonial_MageDoc_Model_Mysql4_Order_Creditmemo_Inquiry
        extends Mage_Sales_Model_Resource_Order_Creditmemo_Item
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'sales_order_creditmemo_item_resource';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('magedoc/creditmemo_inquiry', 'entity_id');
    }
}

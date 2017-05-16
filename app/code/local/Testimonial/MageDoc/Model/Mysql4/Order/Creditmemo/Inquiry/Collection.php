<?php

class Testimonial_MageDoc_Model_Mysql4_Order_Creditmemo_Inquiry_Collection
        extends Mage_Sales_Model_Resource_Order_Creditmemo_Item_Collection
{

    protected function _construct()
    {
        $this->_init('magedoc/order_creditmemo_inquiry');
    }

    public function setCreditmemoFilter($creditmemo)
    {
        if ($creditmemo instanceof Testimonial_MageDoc_Model_Order_Creditmemo) {
            $creditmemoId = $creditmemo->getId();
            if ($creditmemoId) {
                $this->addFieldToFilter('parent_id', $creditmemoId);
            }else{ 
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter('parent_id', $creditmemo);
        }
        return $this;
    }
}

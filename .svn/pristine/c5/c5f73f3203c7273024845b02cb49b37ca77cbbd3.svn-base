<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Aureliano
 * Date: 14.06.13
 * Time: 13:41
 * To change this template use File | Settings | File Templates.
 */
class Testimonial_MageDoc_Block_Adminhtml_Order_View_History extends Mage_Adminhtml_Block_Sales_Order_View_History
{
    public function getSupplyStatuses()
    {
        $supplyStatuses = array($this->getOrder()->getSupplyStatus() => $this->getOrder()->getSupplyStatus());
        switch ($this->getOrder()->getSupplyStatus()){
            case Testimonial_MageDoc_Model_Source_Order_Supply_Status::SHIPPED:
            case Testimonial_MageDoc_Model_Source_Order_Supply_Status::CUSTOMER_NOTIFIED:
            case Testimonial_MageDoc_Model_Source_Order_Supply_Status::DELIVERED:
                $supplyStatuses[Testimonial_MageDoc_Model_Source_Order_Supply_Status::AWAITING_RETURN] = true;
                //$supplyStatuses[Testimonial_MageDoc_Model_Source_Order_Supply_Status::PARTIALLY_RETURNED] = true;
                //$supplyStatuses[Testimonial_MageDoc_Model_Source_Order_Supply_Status::RETURNED] = true;
                break;
            case Testimonial_MageDoc_Model_Source_Order_Supply_Status::AWAITING_RETURN:
                if ($this->getOrder()->hasLastSupplyStatus()){
                    $supplyStatuses[$this->getOrder()->getLastSupplyStatus()] = true;
                }else{
                    $supplyStatuses[Testimonial_MageDoc_Model_Source_Order_Supply_Status::SHIPPED] = true;
                    $supplyStatuses[Testimonial_MageDoc_Model_Source_Order_Supply_Status::CUSTOMER_NOTIFIED] = true;
                }
                break;
        }
        foreach ($supplyStatuses as $key => $value){
            $supplyStatuses[$key] = Mage::helper('magedoc/supply')->getSupplyStatusLabel($key);
        }
        return $supplyStatuses;
    }
}

<?php
class Testimonial_Intime_Model_Carrier_Intime
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'intime';

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @internal param \Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var $result Mage_Shipping_Model_Rate_Result */
       $result = Mage::getModel('shipping/rate_result');
       $shippingPrice = 1.00; // dummy price
       $warehouseId = 1; // dummy warehouse ID
       $warehouseName = 'Склад №1'; // dummy warehouse name

       /** @var $method Mage_Shipping_Model_Rate_Result_Method */
       $method = Mage::getModel('shipping/rate_result_method');
       $method->setCarrier($this->_code)
           ->setCarrierTitle($this->getConfigData('name'))
           ->setMethod('warehouse_' . $warehouseId)
           ->setMethodTitle($warehouseName)
           ->setPrice($shippingPrice)
           ->setCost($shippingPrice);

       $result->append($method);

        return $method;
    }

    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    public function getTrackingInfo($number)
    {
        try {

            $import               = Mage::getModel('intime/import');
            $consignment          = Mage::getModel('intime/consignment');
            $trackingStatus       = Mage::getModel('intime/source_tracking_status');
            $trackingBackDelivery = Mage::getModel('intime/source_tracking_backdelivery');
            
            Mage::helper('intime')->log('Start consignment status synchronization.');
            $import->updateConsignment($consignment->loadByTtn($number));
            Mage::helper('intime')->log('End consignment status synchronization.');
            $consignment->save();
            $statusData = Mage::helper('intime')->__($trackingStatus->getOptionText($consignment->getStatus())) . '<br/>';
            if ($consignment->getRedelivery()){
                $redelivery = Mage::getModel('intime/consignment');
                Mage::helper('intime')->log('Start consignment status update.');
                $import->updateConsignment($redelivery->loadByTtn($consignment->getRedelivery()));
                Mage::helper('intime')->log('End consignment status update.');
                $statusData .= "<b>" . Mage::helper('intime')->__("Redelivery")."</b>: " . $consignment->getRedelivery() . ' - ';
                $redelivery->save();


                $statusData .= Mage::helper('intime')->__($trackingStatus->getOptionText($redelivery->getStatus())) . ' - ';
                if($redelivery->getReceiverCity()){
                    $statusData .= $redelivery->getReceiverCity();
                }
            }
            $statusData .= ($consignment->getPayer()) ? "<b>" . Mage::helper('intime')->__("Payer") . "</b>: " . $consignment->getPayer() . '<br/>' : '';
            $statusData .= ($consignment->getNumPlaces()) ? "<b>" . Mage::helper('intime')->__("Num places") . "</b>: " . $consignment->getNumPlaces() . '<br/>' : '';
            $statusData .= ($consignment->getVolume()) ? "<b>" . Mage::helper('intime')->__("Volume") . "</b>: " . $consignment->getVolume() . '<br/>' : '';

            return new Varien_Object(array(
                'tracking'          => $consignment->getData('ttn'),
                'status'            => $statusData,
                'delivery_location' => $consignment->getData('receiver_city'),
                'deliverydate'      => $consignment->getArrivalDate(),
                'deliverytime'      => '00:00:00',
                'shipped_date'      => $consignment->getData('created_at'),
                'carrier_title'     => Mage::helper('intime')->getStoreConfig('title')
            ));

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('intime')->log("Exception: \n" . $e->__toString());
            throw $e;
        }
    }
}

<?php
class Testimonial_Intime_Model_Observer
{
    public function sales_order_shipment_track_save_after($observer)
    {
        $consignment = Mage::getModel('intime/consignment');
        $import      = Mage::getModel('intime/import');
        $track       = $observer->getEvent()->getTrack();
        $consignment->loadByTtn($track->getTrackNumber());
        if(!$consignment->getId() && $track->dataHasChangedFor('entity_id') && $track->getTrackNumber() && $track->getCarrierCode() == 'intime')
        {
            $consignment->setCustomerId($track->getShipment()->getOrder()->getCustomerId());
            $consignment->setOrderId($track->getOrderId());
            $consignment->setOrderIncrementId($track->getShipment()->getOrder()->getIncrementId());
            $consignment->setShipmentId($track->getParentId());
            $consignment->setTtn(trim($track->getTrackNumber()));
            Mage::helper('intime')->log('Start consignment status update.');
            try {
                $consignment = $import->updateConsignment($consignment);
                Mage::helper('intime')->log('End consignment status update.');
            } catch (Exception $e){
                Mage::helper('intime')->log('Failed to update consignment status.');
            }

            $consignment->save();
        }
    }

    public function sales_order_shipment_track_save_before($observer)
    {
        $track = $observer->getEvent()->getTrack();
        $track->setTrackNumber(trim($track->getTrackNumber()));
    }
}

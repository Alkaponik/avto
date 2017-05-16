<?php
class Ak_NovaPoshta_Model_Observer
{
    public function sales_order_shipment_track_save_after($observer)
    {
        $consignment = Mage::getModel('novaposhta/consignment');
        $import      = Mage::getModel('novaposhta/import');
        $track       = $observer->getEvent()->getTrack();
        $consignment->loadByTtn($track->getTrackNumber());
        if(!$consignment->getId() && $track->dataHasChangedFor('entity_id') && $track->getTrackNumber() && $track->getCarrierCode() == 'novaposhta')
        {
            $consignment->setTrack($track);
            $consignment->setCustomerId($track->getShipment()->getOrder()->getCustomerId());
            $consignment->setOrderId($track->getOrderId());
            $consignment->setOrderIncrementId($track->getShipment()->getOrder()->getIncrementId());
            $consignment->setShipmentId($track->getParentId());
            $consignment->setTtn($track->getTrackNumber());
            Mage::helper('novaposhta')->log('Start consignment status update.');
            $import->updateConsignment($consignment);
            Mage::helper('novaposhta')->log('End consignment status update.');
        }
    }

    public function sales_order_shipment_track_save_before($observer)
    {
        $track = $observer->getEvent()->getTrack();
        $track->setTrackNumber(trim($track->getTrackNumber()));
    }
}

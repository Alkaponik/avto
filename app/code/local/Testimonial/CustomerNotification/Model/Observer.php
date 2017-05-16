<?php

class Testimonial_CustomerNotification_Model_Observer
{
    public function sales_order_shipment_track_save_before(Varien_Event_Observer $observer)
    {
        $track = $observer->getTrack();
        if ($track->getOrigData('entity_id') === null && $track->getNotifyCustomer()){
            Mage::helper('customernotification')->sendTrackInformation($track);
        }
    }

    public function customer_notified_by_sms(Varien_Event_Observer $observer)
    {
        $data = $observer->getPostData();
        $order = $observer->getOrder();
        $history = $observer->getHistory();
        if($data && !empty($data['comment'])){
            Mage::helper('customernotification')->notifiedBySms($data, $order, $history);
        }
    }
}

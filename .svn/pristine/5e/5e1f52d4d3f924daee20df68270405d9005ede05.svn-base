<?php

class Testimonial_MageDoc_Block_Adminhtml_Order_View_Tab_History extends Mage_Adminhtml_Block_Sales_Order_View_Tab_History
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magedoc/order/view/tab/history.phtml');
    }

    /**
     * Status history item manager getter
     *
     * @param array $item
     * @return string
     */
    public function getItemManager(array $item)
    {
        return (isset($item['manager_name']) ? $this->escapeHtml($item['manager_name']) : '');
    }

    /**
     * Compose and get order full history.
     * Consists of the status history comments as well as of invoices, shipments and creditmemos creations
     *
     * @return array
     */
    public function getFullHistory()
    {
        $order = $this->getOrder();

        $history = array();
        foreach ($order->getAllStatusHistory() as $orderComment){
            $history[] = $this->_prepareHistoryItem(
                $orderComment->getStatusLabel(),
                $orderComment->getIsCustomerNotified(),
                $orderComment->getCreatedAtDate(),
                $orderComment->getComment(),
                $orderComment
            );
        }

        foreach ($order->getCreditmemosCollection() as $_memo){
            $history[] = $this->_prepareHistoryItem(
                $this->__('Credit memo #%s created', $_memo->getIncrementId()),
                $_memo->getEmailSent(),
                $_memo->getCreatedAtDate(),
                '',
                $_memo
            );

            foreach ($_memo->getCommentsCollection() as $_comment){
                $history[] = $this->_prepareHistoryItem(
                    $this->__('Credit memo #%s comment added', $_memo->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAtDate(),
                    $_comment->getComment(),
                    $_memo
                );
            }
        }

        foreach ($order->getShipmentsCollection() as $_shipment){
            $history[] = $this->_prepareHistoryItem(
                $this->__('Shipment #%s created', $_shipment->getIncrementId()),
                $_shipment->getEmailSent(),
                $_shipment->getCreatedAtDate(),
                '',
                $_shipment
            );

            foreach ($_shipment->getCommentsCollection() as $_comment){
                $history[] = $this->_prepareHistoryItem(
                    $this->__('Shipment #%s comment added', $_shipment->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAtDate(),
                    $_comment->getComment(),
                    $_shipment
                );
            }
        }

        foreach ($order->getInvoiceCollection() as $_invoice){
            $history[] = $this->_prepareHistoryItem(
                $this->__('Invoice #%s created', $_invoice->getIncrementId()),
                $_invoice->getEmailSent(),
                $_invoice->getCreatedAtDate(),
                '',
                $_invoice
            );

            foreach ($_invoice->getCommentsCollection() as $_comment){
                $history[] = $this->_prepareHistoryItem(
                    $this->__('Invoice #%s comment added', $_invoice->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $_comment->getCreatedAtDate(),
                    $_comment->getComment(),
                    $_invoice
                );
            }
        }

        foreach ($order->getTracksCollection() as $_track){
            $history[] = $this->_prepareHistoryItem(
                $this->__('Tracking number %s for %s assigned', $_track->getNumber(), $_track->getTitle()),
                false,
                $_track->getCreatedAtDate()
            );
        }

        usort($history, array(__CLASS__, "_sortHistoryByTimestamp"));
        return $history;
    }

    /**
     * Map history items as array
     *
     * @param string $label
     * @param bool $notified
     * @param Zend_Date $created
     * @param string $comment
     * @return array
     */
    protected function _prepareHistoryItem($label, $notified, $created, $comment = '', $object = null)
    {
        return array(
            'title'      => $label,
            'notified'   => $notified,
            'comment'    => $comment,
            'created_at' => $created,
            'supply_status' => $object ? $object->getSupplyStatus() : null,
            'status_change_reason' => $object ? $object->getStatusChangeReason() : null,
            'manager_name' => $object instanceof Varien_Object
                                ? $object->getManagerName()
                                : '',
            'is_sugarcrm_call_scheduled' => $object
                ? $object->getIsSugarcrmCallScheduled()
                : null,
            'is_sms_sent' => $object
                ? $object->getIsSmsSent()
                : null,
        );
    }

    /**
     * Comparison For Sorting History By Timestamp
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    private static function _sortHistoryByTimestamp($a, $b)
    {
        $createdAtA = $a['created_at'];
        $createdAtB = $b['created_at'];

        /** @var $createdAta Zend_Date */
        if ($createdAtA->getTimestamp() == $createdAtB->getTimestamp()) {
            return 0;
        }
        return ($createdAtA->getTimestamp() < $createdAtB->getTimestamp()) ? -1 : 1;
    }

    public function isItemSugarcrmCallScheduled(array $item, $isSimpleCheck = true)
    {
        if ($isSimpleCheck) {
            return !empty($item['is_sugarcrm_call_scheduled']);
        }
        return isset($item['is_sugarcrm_call_scheduled']) && false !== $item['is_sugarcrm_call_scheduled'];
    }
}

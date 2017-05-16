<?php

class Testimonial_CustomerNotification_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NOTIFICATION_CHANNEL_SMS = 'sms';
    const NOTIFICATION_CHANNEL_EMAIL = 'email';

    const NOTIFICATION_STATUS_PENDING = 0;
    const NOTIFICATION_STATUS_SUCCESS = 1;
    const NOTIFICATION_STATUS_FAILED = 2;

    const CONFIG_XML_PATH_SMS_PAYMENT_MESSAGE_TEMPLATE = 'customernotification/sms/payment_message_template';
    const CONFIG_XML_PATH_SMS_CONSIGNMENT_MESSAGE_TEMPLATE = 'customernotification/sms/consignment_message_template';
    const CONFIG_XML_PATH_SMS_ENABLED = 'customernotification/sms/enabled';
    const CONFIG_XML_PATH_SMS_LOG_ENABLED = 'customernotification/sms/log_enabled';

    const ENTITY_TYPE_INVOICE = 'sales/order_invoice';
    const ENTITY_TYPE_SHIPMENT_TRACK = 'sales/order_shipment_track';

    public function canSendInvoiceSms($invoice)
    {
        $storeId = $invoice->getStoreId();
        $channels = $this->getNotificationChannels($storeId);
        return in_array(self::NOTIFICATION_CHANNEL_SMS, $channels)
            && $invoice->getOrder()->getPayment()->getMethod() == 'checkmo';
    }

    public function sendPaymentDetails($invoice)
    {
        $eventType = $this->__('Payment Details');
        $storeId = $invoice->getStoreId();
        $channels = $this->getNotificationChannels($storeId);
        $status = false;
        if (in_array(self::NOTIFICATION_CHANNEL_SMS, $channels)){
            $smsMessage = $this->_processTemplate($this->getSmsPaymentMessageTemplate($storeId), $invoice);
            $telephone = $invoice->getOrder()->getShippingAddress()->getTelephone();
            if (Mage::getModel('smsgateway/api')->sendMessage($smsMessage, $telephone, $storeId)) {
                $comment = $this->__('Notified customer by SMS (%s) (%s)', $telephone, $eventType);
                $status = self::NOTIFICATION_STATUS_SUCCESS;
            } else {
                $comment = $this->__('FAILED to notify customer by SMS (%s) (%s)', $telephone, $eventType);
                $status = self::NOTIFICATION_STATUS_FAILED;
            }
            // add comment about notification to order object
            $invoice->getOrder()->addStatusHistoryComment($comment)->save();
            if ($this->isSmsLogEnabled($storeId)){
                $order = $invoice->getOrder();
                $message = Mage::getModel('customernotification/message');
                $message->setData(array(
                    'channel'   =>  self::NOTIFICATION_CHANNEL_SMS,
                    'status'    =>  $status,
                    'event'     =>  $eventType,
                    'recipient' =>  $telephone,
                    'text'      =>  $smsMessage,
                    'store_id'  =>  $storeId,
                    'order_id'  =>  $order->getId(),
                    'order_increment_id'  => $order->getIncrementId(),
                    'customer_id'   =>  $order->getCustomerId(),
                    'customer_name' =>  $order->getCustomerName(),
                    'entity_type'   =>  self::ENTITY_TYPE_INVOICE,
                    'entity_id'     =>  $invoice->getId(),
                    'attempt_count' =>  1,
                    'success_count' =>  $status === self::NOTIFICATION_STATUS_SUCCESS,
                ));
                $message->save();
            }
        }
        return $status;
    }

    public function sendShipmentTracking($shipment)
    {
        foreach ($shipment->getAllTracks() as $track){
            $this->sendTrackInformation($track);
        }
    }

    public function sendTrackInformation($track)
    {
        $eventType = $this->__('Consignment Note');
        $shipment = $track->getShipment();
        $storeId = $shipment->getStoreId();
        $channels = $this->getNotificationChannels($storeId);
        $status = false;
        if (in_array(self::NOTIFICATION_CHANNEL_SMS, $channels)){
            $smsMessage = $this->_processTemplate($this->getSmsConsignmentMessageTemplate($storeId), $track);
            $telephone = $shipment->getOrder()->getShippingAddress()->getTelephone();
            $order = $shipment->getOrder();
            $supplyStatus = false;
            if (Mage::getModel('smsgateway/api')->sendMessage($smsMessage, $telephone, $storeId)) {
                $comment = $this->__('Notified customer by SMS (%s) (%s)', $telephone, $eventType);
                $status = self::NOTIFICATION_STATUS_SUCCESS;
                /**
                 * @todo: check whether all shipment have tracking codes and all trackings were sent
                 */
                if ($order->isAllItemsQtyShipped()){
                    $order->updateSupplyStatus();
                    $supplyStatus = Testimonial_MageDoc_Model_Source_Order_Supply_Status::CUSTOMER_NOTIFIED;
                }
                $track->setSmsSent(true);
            } else {
                $comment = $this->__('FAILED to notify customer by SMS (%s) (%s)', $telephone, $eventType);
                $status = self::NOTIFICATION_STATUS_FAILED;
            }
            // add comment about notification to order object
            $order->addStatusHistoryComment($comment, false, $supplyStatus);
            $order->save();
            if ($this->isSmsLogEnabled($storeId)){
                $order = $shipment->getOrder();
                $message = Mage::getModel('customernotification/message');
                $message->setData(array(
                    'channel'   =>  self::NOTIFICATION_CHANNEL_SMS,
                    'status'    =>  $status,
                    'event'     =>  $eventType,
                    'recipient' =>  $telephone,
                    'text'      =>  $smsMessage,
                    'store_id'  =>  $storeId,
                    'order_id'  =>  $order->getId(),
                    'order_increment_id'  => $order->getIncrementId(),
                    'customer_id'   =>  $order->getCustomerId(),
                    'customer_name' =>  $order->getCustomerName(),
                    'entity_type'   =>  self::ENTITY_TYPE_SHIPMENT_TRACK,
                    'entity_id'     =>  $track->getId(),
                    'attempt_count' =>  1,
                    'success_count' =>  $status === self::NOTIFICATION_STATUS_SUCCESS,
                ));
                $message->save();
            }
        }
        return $status;
    }

    public function notifiedBySms($postData, $order, $history)
    {
        $eventType = $this->__('Custom');
        $storeId = $order->getStoreId();
        $channels = $this->getNotificationChannels($storeId);
        $status = false;
        if (in_array(self::NOTIFICATION_CHANNEL_SMS, $channels)){
            $smsMessage = $comment = trim(strip_tags($postData['comment']));
            $telephone = $order->getShippingAddress()->getTelephone();

            if (Mage::getModel('smsgateway/api')->sendMessage($smsMessage, $telephone, $storeId)) {
                $history->setIsSmsSent(1);
                $status = true;
            } else {
                $history->setIsSmsSent(0);
            }
            $history->save();
            $order->save();
            if ($this->isSmsLogEnabled($storeId)){
                $message = Mage::getModel('customernotification/message');
                $message->setData(array(
                    'channel'   =>  self::NOTIFICATION_CHANNEL_SMS,
                    'status'    =>  $status,
                    'event'     =>  $eventType,
                    'recipient' =>  $telephone,
                    'text'      =>  $smsMessage,
                    'store_id'  =>  $storeId,
                    'order_id'  =>  $order->getId(),
                    'order_increment_id'  => $order->getIncrementId(),
                    'customer_id'   =>  $order->getCustomerId(),
                    'customer_name' =>  $order->getCustomerName(),
                    'attempt_count' =>  1,
                    'success_count' =>  $status === self::NOTIFICATION_STATUS_SUCCESS,
                ));
                $message->save();
            }
        }
        return $status;
    }

    public function getNotificationChannels($storeId = null)
    {
        $channels = array();
        if (Mage::getStoreConfig(self::CONFIG_XML_PATH_SMS_ENABLED, $storeId)){
            $channels []= self::NOTIFICATION_CHANNEL_SMS;
        }
        return $channels;
    }

    public function getSmsPaymentMessageTemplate($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_SMS_PAYMENT_MESSAGE_TEMPLATE, $storeId);
    }

    public function getSmsConsignmentMessageTemplate($storeId = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_SMS_CONSIGNMENT_MESSAGE_TEMPLATE, $storeId);
    }

    public function isSmsLogEnabled($storeId)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_SMS_LOG_ENABLED, $storeId);
    }

    protected function _processTemplate($expression, $dataObject)
    {
        preg_match_all('/\{\{([^\}]+)\}\}/', $expression, $expressions);
        if (!empty($expressions)){
            for ($index = 0, $num = count($expressions[1]); $index < $num; $index++){
                $expression = str_replace("{{" . $expressions[1][$index] . "}}", $this->_executeExpression($dataObject, $expressions[1][$index]), $expression);
            }
        }
        return $expression;
    }

    protected function _executeExpression($dataObject, $node)
    {
        /* Parsing internal XPATH format which defines the data */
        $actions = explode('/',$node);
        $data = $dataObject;
        foreach ($actions as $action) {
            switch(true) {
                /* dataObject attribute handling */
                case substr($action,0,1)=='@':
//                            print_r(get_class($data));
                    $data = ($data instanceof Varien_Object) ? $data->getData(substr($action,1)) : null;
//                            print_r("@$key=".substr($action,1)."<br/>");
                    break;
                /* Current dataObject, doing nothing */
                case $action=='.':
                    break;
                /* Parent handling */
                case $action=='..':
                    /**
                     * @todo: exclude the parent gathering into the
                     * separate getParent($dataObject) function
                     */
                    switch(true) {
                        case $dataObject instanceof Mage_Sales_Model_Order_Address:
                        case $dataObject instanceof Mage_Sales_Model_Order_Item:
                            $data=$data->getOrder();
                            break;
                    }
                    break;
                /* Function call */
                case substr($action,-1)==')':
                    $function = substr($action,0,strpos($action,'('));
                    $params = array();
                    /**
                     *  @todo implement complex argument reference handling
                     */
                    $args = substr($action,strpos($action,'(')+1,-1);
                    if ($args) {
                        $args = explode(',',$args);
                        foreach($args as $arg) {
                            $arg = trim($arg);
                            if (substr($arg,0,1)=='@') {
                                if (strlen($arg)>1 && $data instanceof Varien_Object) {
                                    $params[]=$data->getData(substr($arg,1));
                                }else {
                                    $params[] = &$data;
                                }
                            }else {
                                $params[]=trim($arg, '\'"');
                            }
                        }
                    }
                    if (strpos($function, ':') === false){
                        $callable = array(&$data,$function);
                    }else if (strpos($function, '::') === false){
                        $callable = $callable = explode(':',$function);
                    }else{
                        $callable = explode('::',$function);
                        $callable[0] = &$this;
                    }

//                            print_r(get_class($callable[0])."::{$callable[1]}():".'<br/>');
                    if (!$callable[0]){
                        $callable = $callable[1];
                    }
                    if (is_callable($callable)){
                        $data = call_user_func_array($callable, $params);
                    }else{
                        if (is_array($callable)){
                            $callable = get_class($callable[0]). '::' . $callable[1];
                        }
                        self::log('Unable to call '. $callable);
                        $data = null;
                    }

                    if(is_object($data)) {
//                                print_r(get_class($data).'<br/>');
                    }else {
//                                print_r($data.'<br/>');
                    }
                    break;
                /* Constant value handling */
                case strlen($action):
                default:
                    if (!is_string($data)) {
                        $data = $action;
                    }
                    break;
            }
        }
        return $data;
    }

    public function getCurrentUser()
    {
        return $user = Mage::getSingleton('admin/session')->getUser();
    }
}
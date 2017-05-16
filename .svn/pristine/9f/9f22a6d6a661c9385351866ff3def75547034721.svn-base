<?php
class Testimonial_SugarCRM_Helper_Call extends Mage_Core_Helper_Abstract
{
    const CONFIG_XML_PATH_DEFAULT_CALL_TITLE = 'sugarcrm/general/call_title';
    const CONFIG_XML_PATH_DEFAULT_CALL_DESCRIPTION = 'sugarcrm/general/call_description';
    const CONFIG_XML_PATH_DEFAULT_ALLOW_USERS_TO_CREATE_CALLS = 'sugarcrm/general/allow_users_to_create_calls';

    protected $_templateProcessor = null;

    public function exportCallToSugarcrm( $history )
    {
        $order = $history->getOrder();

        $storeId = $order->getStoreId();

        if(!Mage::getStoreConfig(self::CONFIG_XML_PATH_DEFAULT_ALLOW_USERS_TO_CREATE_CALLS, $storeId) ) {
            return;
        }

        if( is_null( $order->getCustomer() ) ) {
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $order->setCustomer( $customer );
        }
        $hlp = Mage::helper('sugarcrm');

        try {
            $hlp->log('CRM Call Export');
            $client = $hlp->getSoapClient($storeId);

            $sessionId = $hlp->getSoapSessionId();
            $managerSugarcrmUserId = Mage::getModel('admin/user')->load($order->getManagerId())->getSugarcrmUserId();

            if( !$managerSugarcrmUserId ){
                Mage::throwException($this->__('Current order manager doesn\'t have related SugarCRM account defined'));
            }

            $users = array($managerSugarcrmUserId);

            $callDateTime = $history->getCallDateTime();
            $dateStamp = Mage::getModel('core/date')->gmtTimestamp($callDateTime);
            $callInterval = $hlp->getDefaultCallInterval($storeId);
            $callDuration = $hlp->getDefaultCallDuration($storeId);

            $callData = array(
                array(
                    'name' => 'name',
                    'value' => $this->_getCallTitle($history),
                ),
                array(
                    'name' => 'description',
                    'value' => $this->_getCallDescription($history),
                ),
                array(
                    'name' => 'direction',
                    'value' => 'Outbound',
                ),
                array(
                    'name' => 'status',
                    'value' => 'Planned',
                ),
                array(
                    'name' => 'assigned_user_id',
                    'value' => $managerSugarcrmUserId,
                ),
                array(
                    'name' => 'date_start',
                    'value' => date('Y-m-d H:i:s', $dateStamp),
                ),
                array(
                    'name' => 'date_end',
                    'value' => date('Y-m-d H:i:s', $dateStamp + 60 * $callInterval),
                ),
                array(
                    'name' => 'duration_minutes',
                    'value' => $callDuration,
                ),
            );

            if ($order->getCustomer( )->getSugarcrmContactId()){
                $callData[] =  array(
                    'name' => 'parent_type',
                    'value' => 'Contacts',
                );
                $callData[] =  array(
                    'name' => 'parent_id',
                    'value' => $order->getCustomer( )->getSugarcrmContactId(),
                );
            }

            if( $currentAdminUserSugarcrmUserId = $hlp->getCurrentUser()->getSugarcrmUserId() ) {
                $callData[] =  array(
                    'name' => 'created_by',
                    'value' => $currentAdminUserSugarcrmUserId,
                );
                $callData[] =  array(
                    'name' => 'set_created_by',
                    'value' => false,
                );
                if ($currentAdminUserSugarcrmUserId != $managerSugarcrmUserId){
                    $users[] = $currentAdminUserSugarcrmUserId;
                }
            }

            $response = $client->set_entry($sessionId, 'Calls', $callData);
            $callId = is_object($response)
                ? $response->id
                : null;
            if (is_null($callId) || $callId == -1){
                $message = is_object($response) && !empty($response->error)
                    ? $response->error->name
                    : '';
                Mage::throwException($this->__('Unable to schedule call: %s', $message));
            } else {
                $history->setIsSugarcrmCallScheduled(1);
                $history->save();
            }

            if( $customerSugarcrmContactId = $order->getCustomer( )->getSugarcrmContactId() ) {
                $response = $client->set_relationship( $sessionId, 'Contacts', $customerSugarcrmContactId, 'calls',
                    array(
                        $callId
                    )
                );

                $response = $client->set_relationship( $sessionId, 'Calls', $callId, 'users',
                    $users
                );
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if (!empty($client)){
                $hlp->log($client->__getLastRequest());
                $hlp->log($client->__getLastResponse());
            };
        }
    }

    protected function _getCallTitle( $history, $store = null ) {
        $title =  Mage::getStoreConfig(self::CONFIG_XML_PATH_DEFAULT_CALL_TITLE, $store);
        return $this->_getTemplateProcessor()->processTemplate($title, $history);
    }

    protected function _getCallDescription( $history, $store = null ) {
        $description =  Mage::getStoreConfig(self::CONFIG_XML_PATH_DEFAULT_CALL_DESCRIPTION, $store);
        return $this->_getTemplateProcessor()->processTemplate($description, $history);
    }

    protected function _getTemplateProcessor() {
        if( is_null($this->_templateProcessor) ) {
            $this->_templateProcessor = Mage::helper('magedoc_system');
        }
        return $this->_templateProcessor;
    }

}
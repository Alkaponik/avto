<?php
class Testimonial_SugarCRM_Helper_Order extends Mage_Core_Helper_Abstract
{
    private static $exportedOrderId = null;
    const CONFIG_XML_PATH_DEFAULT_ENABLE_ORDER_EXPORT = 'sugarcrm/general/enable_order_export';

    public function exportOrderToSugarcrm( $order )
    {
        $storeId = $order->getStoreId();

        if( !Mage::getStoreConfig(self::CONFIG_XML_PATH_DEFAULT_ENABLE_ORDER_EXPORT, $storeId) ) {
            return;
        }

        if( $order->isObjectNew() ) {
            $customer = $order->getCustomer();
            $hlp = Mage::helper('sugarcrm');

            try {
                $hlp->log('CRM Order Export');
                $client = $hlp->getSoapClient($storeId);
                $sessionId = $hlp->getSoapSessionId();

                $response = $client->get_entry_list($sessionId, 'Opportunities');

                $orderData = array(
                    array (
                        "name" => 'name',
                        "value" => "Заказ-".$order->getId(),
                    ),
                    array(
                        "name" => 'assigned_user_id',
                        "value" => 1
                    ),
                    array(
                        "name" => 'amount',
                        "value" => $order->getGrandTotal(),
                    ),
                    array(
                        "name" => 'amount_usdollar',
                        "value" => $order->getGrandTotal(),
                    ),
                );

                if( $currentAdminUserSugarcrmUserId = $hlp->getCurrentUser()->getSugarcrmUserId() ) {
                    $callData[] =  array('name' => 'created_by','value' => $currentAdminUserSugarcrmUserId, );
                    $callData[] =  array('name' => 'set_created_by','value' => false,);
                }

                $response = $client->set_entry($sessionId, 'Opportunities', $orderData);

                if( !is_null($customer) && $customerSugarcrmContactId = $customer->getSugarcrmContactId() ) {
                    $response = $client->set_relationship( $sessionId, 'Contacts', $customerSugarcrmContactId, 'opportunities', array( $response->id ) );
                }

                self::$exportedOrderId = $order->getId();

            } catch (Exception $e) {
                Mage::logException($e);
                if (!empty($client)){
                    $hlp->log($client->__getLastRequest());
                    $hlp->log($client->__getLastResponse());
                };
            }
        }

    }
}
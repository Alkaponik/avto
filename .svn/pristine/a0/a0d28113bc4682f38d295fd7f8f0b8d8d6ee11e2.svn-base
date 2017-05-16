<?php

class Testimonial_SugarCRM_Helper_Customer extends Mage_Core_Helper_Abstract
{
    public function exportCustomerToSugarcrm( $customer )
    {
        $storeId = $customer->getStoreId();

        $hlp = Mage::helper('sugarcrm');

        $defaultBillingAddress = $customer->getDefaultBillingAddress();
        $defaultShippingAddress = $customer->getDefaultShippingAddress();

        if($defaultBillingAddress) {
            $primaryContactAddress = $defaultBillingAddress;
            if( $defaultShippingAddress ) {
                $altContactAddress = $defaultShippingAddress;
            }
        } else  {
            if( $defaultShippingAddress ) {
                $primaryContactAddress = $defaultShippingAddress;
            } else {
                $addresses = $customer->getAddressesCollection();
                $counter = 0;
                foreach($addresses as $address) {
                    if($counter == 0) {
                        $primaryContactAddress = $address;
                    } elseif($counter == 1) {
                        $altContactAddress = $address;
                        break;
                    }
                    $counter++;
                }
            }
        }

        if ($hlp->isCustomerExportEnabled($storeId)
            && $customer->getId()
            && !$customer->getSugarcrmContactId()
            && isset($primaryContactAddress)
            && $telephone = preg_replace('/[^0-9]/', '', $primaryContactAddress->getTelephone())) {

            try {
                $hlp->log('CRM Customer Export');
                $client = $hlp->getSoapClient($storeId);
                $sessionId = $hlp->getSoapSessionId();

                // look for a particular account name and then get its ID
                $hlp->log($this->_getContactLookupWhereStatement($telephone));
                $response = $client->get_entry_list($sessionId, 'Contacts', $this->_getContactLookupWhereStatement($telephone));

                if (empty($response->entry_list[0]) || !$contactId = $response->entry_list[0]->id) {
                    // create a new contact record, assigned to this account, and grab the contact ID

                    $contactData = array(
                        array("name" => 'first_name', "value" => $customer->getFirstname()),
                        array("name" => 'last_name', "value" => $customer->getLastname()),
                        array("name" => 'email1', "value" => $customer->getEmail()),
                        array("name" => 'phone_mobile', "value" => $telephone),
                        array("name" => 'phone_work', "value" => $primaryContactAddress->getFax()),
                        array("name" => 'primary_address_street', "value" => implode(", ", $primaryContactAddress->getStreet())),
                        array("name" => 'primary_address_city', "value" => $primaryContactAddress->getCity()),
                        array("name" => 'primary_address_country', "value" => $primaryContactAddress->getCountry()),
                        array("name" => 'primary_address_state', "value" => $primaryContactAddress->getRegion()),
                        array("name" => 'primary_address_postalcode', "value" => $primaryContactAddress->getPostCode()),
                    );

                    if(isset($altContactAddress) && $altContactAddress->getId() != $primaryContactAddress->getId() ) {
                        $contactData[] = array("name" => 'alt_address_street', "value" => implode(", ", $altContactAddress->getStreet()));
                        $contactData[] = array("name" => 'alt_address_city', "value" => $altContactAddress->getCity());
                        $contactData[] = array("name" => 'alt_address_country', "value" => $altContactAddress->getCountry());
                        $contactData[] = array("name" => 'alt_address_state', "value" => $altContactAddress->getRegion());
                        $contactData[] = array("name" => 'alt_address_postalcode', "value" => $altContactAddress->getPostCode());
                    }

                    if ($userId = $hlp->getCurrentUser()->getSugarcrmUserId()){
                        $contactData[] = array("name" => 'assigned_user_id', "value" => $userId);
                        $contactData[] =  array('name' => 'created_by', 'value' => $userId );
                        $contactData[] =  array('name' => 'set_created_by','value' => false);
                    }

                    $response = $client->set_entry($sessionId, 'Contacts', $contactData);

                    if (!is_object($response) || $response->id == -1){
                        $message = is_object($response) && !empty($response->error)
                            ? $response->error->name
                            : '';
                        Mage::throwException($this->__('Unable to create contact: %s', $message));
                    }

                    $contactId = $response->id;
                    $hlp->log(sprintf('Contact saved: %s', $contactId));
                }else{
                    $hlp->log(sprintf('Contact already exists: %s', $contactId));
                }

                if (!empty($contactId)){
                    $customer->setSugarcrmContactId($contactId);
                    $hlp->saveStaticAttributes($customer, array('sugarcrm_contact_id'));
                }

            } catch (Exception $e) {
                Mage::logException($e);
                $hlp->log($hlp->__('Customer export failed'));
                if (!empty($client)){
                    $hlp->log($client->__getLastRequest());
                    $hlp->log($client->__getLastResponse());
                };
            }
        }
    }

    protected function _getContactLookupWhereStatement($phoneToFind)
    {
        $sqlReplace = "contacts.%s LIKE '%s%%'";
        $wherePortion = "(";
        $wherePortion .= sprintf($sqlReplace, "phone_work", $phoneToFind) . " OR ";
        $wherePortion .= sprintf($sqlReplace, "phone_home", $phoneToFind) . " OR ";
        $wherePortion .= sprintf($sqlReplace, "phone_other", $phoneToFind) . " OR ";
        $wherePortion .= sprintf($sqlReplace, "assistant_phone", $phoneToFind) . " OR ";
        $wherePortion .= sprintf($sqlReplace, "phone_mobile", $phoneToFind) . ") and contacts.deleted='0'";
        return $wherePortion;
    }


}

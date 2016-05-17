<?php

include_once 'ImporterAbstract.php';

/**
 * Import contacts
 *
 * @author rmroz
 */
class ContactImporter extends ImporterAbstract {
   
    
    
    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     * Get list of contacts added from last succesfull import
     */
    public function importContactList()
    {
        // filters
        $params = array('complex_filter'=>
            array(
                array('key'=>'created_at','value'=>array('key' =>'from','value' => $this->getImportTimeLimit()))
            )
        );

        $resultClients = $this->soapClient->customerCustomerList($this->sessionId, $params);

        foreach ( $resultClients as  $client) {
            
            // check if user exist 
            $found = false;
            $sea = new SugarEmailAddress();
            $results = $sea->getBeansByEmailAddress($client->email);
            foreach ($results as $aBean) {
                if ($aBean instanceof Contact && $aBean->deleted == 0) {
                    $found = true;
                    break;
                }
            }

            // if user already exist continue to another user
            if ( $found ) {
                continue;
            }
            
            // import contact
            $this->importContact($client);
            
        }
    }
    
    
    /**
     * 
     * @param stdClass $client
     */
    public function importContact( $client )
    {
        
        $clientAddr = $this->soapClient->customerAddressList($this->sessionId, $client->customer_id);
            
            // fields from magento not match exacly structure of database
            // from sugarcrm i.e. phone number is in address in magento but
            // in sugar crm is in main contact so we fill this fields in sugar:
            // First address we get:
            //      from magento "default billing address". 
            // Second address we get:
            //      from magento "default shipping address"
            $defaultBilling = null;
            $defaultShipping = null;
            foreach ( $clientAddr as $addr ) {
                if ( $addr->is_default_billing == 1 ) {
                    $defaultBilling = $addr;
                } else if ($addr->is_default_shipping == 1) {
                    $defaultShipping = $addr;
                }
            }
        
            // Add contact with adresses
            $contactBean = new Contact();
            $contactBean->first_name = $client->firstname;
            $contactBean->last_name = $client->lastname;
            $contactBean->phone_mobile = $defaultBilling->telephone;
            $contactBean->phone_other = $defaultShipping->telephone;

                // primary address
                $contactBean->primary_address_street = $defaultBilling->street;
                $contactBean->primary_address_city = $defaultBilling->city;
                $contactBean->primary_address_state = $defaultBilling->region;
                $contactBean->primary_address_postalcode = $defaultBilling->postcode;
                $contactBean->primary_address_country = $defaultBilling->country_id;

                // alt address
                $contactBean->alt_address_street = $defaultShipping->street;
                $contactBean->alt_address_city = $defaultShipping->city;
                $contactBean->alt_address_state = $defaultShipping->region;
                $contactBean->alt_address_postalcode = $defaultShipping->postcode;
                $contactBean->alt_address_country = $defaultShipping->country_id;
                $contactBean->email1 = $client->email;
                $contactBean->save();

            // Add account relation 
            $focus = new Account();
            $focus->retrieve(self::SUPPLIER_ID);
            $focus->load_relationship('contacts');
            $focus->contacts->add($contactBean->id);
            $focus->save();
    }
    
    
    
}

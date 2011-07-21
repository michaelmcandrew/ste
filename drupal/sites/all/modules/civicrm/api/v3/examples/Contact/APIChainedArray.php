<?php



/*
 /*this demonstrates the usage of chained api functions. In this case no notes or custom fields have been created 
 */
function contact_get_example(){
$params = array( 
  'id' => 1,
  'version' => 3,
  'api.website.get' => array(),
  'api.Contribution.get' => array( 
      'total_amount' => '120.00',
    ),
  'api.CustomValue.get' => 1,
  'api.Note.get' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'contact','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'contact_id' => '1',
          'contact_type' => 'Individual',
          'sort_name' => 'xyz3, abc3',
          'display_name' => 'abc3 xyz3',
          'do_not_email' => '',
          'do_not_phone' => '',
          'do_not_mail' => '',
          'do_not_sms' => '',
          'do_not_trade' => '',
          'is_opt_out' => '',
          'preferred_mail_format' => 'Both',
          'first_name' => 'abc3',
          'last_name' => 'xyz3',
          'is_deceased' => '',
          'contact_is_deleted' => '',
          'email_id' => '1',
          'email' => 'man3@yahoo.com',
          'on_hold' => '',
          'api.website.get' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 1,
              'id' => 6,
              'values' => array( 
                  '0' => array( 
                      'id' => '6',
                      'contact_id' => '1',
                      'url' => 'http://civicrm.org',
                    ),
                ),
            ),
          'api.Contribution.get' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 1,
              'id' => 5,
              'values' => array( 
                  '0' => array( 
                      'contact_id' => '1',
                      'contact_type' => 'Individual',
                      'sort_name' => 'xyz3, abc3',
                      'display_name' => 'abc3 xyz3',
                      'contribution_id' => '5',
                      'currency' => 'USD',
                      'receive_date' => '2011-01-01 00:00:00',
                      'non_deductible_amount' => '10.00',
                      'total_amount' => '120.00',
                      'fee_amount' => '50.00',
                      'net_amount' => '90.00',
                      'trxn_id' => '12335',
                      'invoice_id' => '67830',
                      'contribution_source' => 'SSF',
                      'is_test' => '',
                      'is_pay_later' => '',
                      'contribution_type_id' => '1',
                      'contribution_type' => 'Donation',
                      'instrument_id' => '68',
                      'payment_instrument' => 'Credit Card',
                      'contribution_status_id' => '1',
                      'contribution_status' => 'Completed',
                      'contribution_payment_instrument' => 'Credit Card',
                    ),
                ),
            ),
          'api.CustomValue.get' => array( 
              'is_error' => 1,
              'error_message' => 'No values found for the specified entity ID and custom field(s).',
            ),
          'api.Note.get' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 0,
              'values' => array(),
            ),
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* contact_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/
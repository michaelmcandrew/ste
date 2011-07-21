<?php



/*
 
 */
function membership_status_get_example(){
$params = array( 
  'name' => 'test status',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'membership_status','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_status_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 10,
  'values' => array( 
      '10' => array( 
          'id' => '10',
          'name' => 'test status',
          'label' => 'test status',
          'start_event' => 'start_date',
          'end_event' => 'end_date',
          'is_current_member' => '1',
          'is_admin' => '',
          'is_default' => '',
          'is_active' => '1',
          'is_reserved' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* membership_status_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/
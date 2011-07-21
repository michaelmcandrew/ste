<?php



/*
 This demonstrates use of the 'format.single_entity_array' param. 
    /* This param causes the only contact to be returned as an array without the other levels.
    /* it will be ignored if there is not exactly 1 result
 */
function contact_get_example(){
$params = array( 
  'version' => 3,
  'id' => 17,
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
  'contact_id' => '17',
  'contact_type' => 'Individual',
  'display_name' => 'Test Contact',
  'is_opt_out' => '',
  'first_name' => 'Test',
  'last_name' => 'Contact',
  'contact_is_deleted' => '',
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
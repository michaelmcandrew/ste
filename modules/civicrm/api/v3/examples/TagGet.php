<?php



/*
 
 */
function tag_get_example(){
$params = array( 
  'id' => 6,
  'name' => 'New Tag38741',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'tag','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function tag_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 6,
  'values' => array( 
      '6' => array( 
          'id' => '6',
          'name' => 'New Tag38741',
          'description' => 'This is description for New Tag 32762',
          'parent_id' => '',
          'is_selectable' => '1',
          'is_reserved' => '',
          'is_tagset' => '',
          'used_for' => 'civicrm_contact',
          'created_id' => '',
          'created_date' => '2011-06-02 02:39:21',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* tag_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/
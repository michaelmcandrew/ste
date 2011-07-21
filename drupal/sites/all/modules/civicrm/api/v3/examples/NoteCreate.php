<?php



/*
 
 */
function note_create_example(){
$params = array( 
  'entity_table' => 'civicrm_contact',
  'entity_id' => 9,
  'note' => 'Hello!!! m testing Note',
  'contact_id' => 9,
  'modified_date' => '2011-01-31',
  'subject' => 'Test Note',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'note','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function note_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 10,
  'values' => array( 
      '10' => array( 
          'id' => 10,
          'entity_table' => 'civicrm_contact',
          'entity_id' => 9,
          'note' => 'Hello!!! m testing Note',
          'contact_id' => 9,
          'modified_date' => '2011-01-31',
          'subject' => 'Test Note',
          'privacy' => 0,
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* note_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/
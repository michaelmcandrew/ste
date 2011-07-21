<?php



/*
 
 */
function option_value_get_example(){
$params = array( 
  'option_group_id' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'option_value','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function option_value_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 5,
  'values' => array( 
      '1' => array( 
          'id' => '1',
          'option_group_id' => '1',
          'label' => 'Phone',
          'value' => '1',
          'filter' => '',
          'weight' => '1',
          'is_optgroup' => '',
          'is_reserved' => '',
          'is_active' => '1',
        ),
      '2' => array( 
          'id' => '2',
          'option_group_id' => '1',
          'label' => 'Email',
          'value' => '2',
          'filter' => '',
          'weight' => '2',
          'is_optgroup' => '',
          'is_reserved' => '',
          'is_active' => '1',
        ),
      '3' => array( 
          'id' => '3',
          'option_group_id' => '1',
          'label' => 'Postal Mail',
          'value' => '3',
          'filter' => '',
          'weight' => '3',
          'is_optgroup' => '',
          'is_reserved' => '',
          'is_active' => '1',
        ),
      '4' => array( 
          'id' => '4',
          'option_group_id' => '1',
          'label' => 'SMS',
          'value' => '4',
          'filter' => '',
          'weight' => '4',
          'is_optgroup' => '',
          'is_reserved' => '',
          'is_active' => '1',
        ),
      '5' => array( 
          'id' => '5',
          'option_group_id' => '1',
          'label' => 'Fax',
          'value' => '5',
          'filter' => '',
          'weight' => '5',
          'is_optgroup' => '',
          'is_reserved' => '',
          'is_active' => '1',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* option_value_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/
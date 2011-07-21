<?php



/*
 
 */
function event_update_example(){
$params = array( 
  'title' => 'Annual CiviCRM meet',
  'summary' => 'If you have any CiviCRM realted issues or want to track where CiviCRM is heading, Sign up now',
  'description' => 'This event is intended to give brief idea about progess of CiviCRM and giving solutions to common user issues',
  'event_type_id' => 1,
  'is_public' => 1,
  'start_date' => 20081021,
  'end_date' => 20081023,
  'is_online_registration' => 1,
  'registration_start_date' => 20080601,
  'registration_end_date' => 20081015,
  'max_participants' => 100,
  'event_full_text' => 'Sorry! We are already full',
  'is_monetory' => 0,
  'is_active' => 1,
  'is_show_location' => 0,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'event','update',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function event_update_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 15,
  'values' => array( 
      '15' => array( 
          'id' => '15',
          'title' => 'Annual CiviCRM meet',
          'event_title' => 'Annual CiviCRM meet',
          'summary' => 'If you have any CiviCRM realted issues or want to track where CiviCRM is heading, Sign up now',
          'description' => 'This event is intended to give brief idea about progess of CiviCRM and giving solutions to common user issues',
          'event_description' => 'This event is intended to give brief idea about progess of CiviCRM and giving solutions to common user issues',
          'event_type_id' => '1',
          'participant_listing_id' => '',
          'is_public' => '1',
          'start_date' => '2008-10-21 00:00:00',
          'event_start_date' => '2008-10-21 00:00:00',
          'end_date' => '2008-10-23 00:00:00',
          'event_end_date' => '2008-10-23 00:00:00',
          'is_online_registration' => '1',
          'registration_start_date' => '2008-06-01 00:00:00',
          'registration_end_date' => '2008-10-15 00:00:00',
          'max_participants' => '150',
          'event_full_text' => 'Sorry! We are already full',
          'is_monetary' => '',
          'contribution_type_id' => '',
          'is_map' => '',
          'is_active' => '1',
          'is_show_location' => '',
          'default_role_id' => '1',
          'is_email_confirm' => '',
          'is_pay_later' => '',
          'is_multiple_registrations' => '',
          'allow_same_participant_emails' => '',
          'is_template' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* event_update 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/
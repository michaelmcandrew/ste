<?php
require_once '../civicrm.config.php';
require_once 'CRM/Core/Config.php';
require_once ('CRM/nusoap/nusoap.php');
//require_once 'CRM/Utils/SoapServer.php';
require_once 'CRM/Utils/ContactsForOutlook.php';
/**************************************************************
 *  Description:
 *  Creates a simple SOAP Server (server.php).
 **************************************************************/

// includes nusoap classes
// create server
$SoapServer = new nusoap_server();

// wsdl generation
$SoapServer->debug_flag = false;
$SoapServer->configureWSDL('CiviSyncJoomlaWebService', 'http://civicrm.com/');
$SoapServer->wsdl->schemaTargetNamespace = 'http://civicrm.com/';

//Input Rows class
$SoapServer->wsdl->addComplexType('Rows', 'complexType', 'struct', 'all', '', array (
    'offset' => array (
    'name' => 'offset',
    'type' => 'xsd:int'
    ),
    'rowCount' => array (
    'name' => 'rowCount',
    'type' => 'xsd:int'
    )
));

// Output Contact Class
$SoapServer->wsdl->addComplexType('Contact', 'complexType', 'struct', 'all', '', array (
    'first_name' => array (
    'name' => 'first_name',
    'type' => 'xsd:string'
    ),
    'middle_name' => array (
    'name' => 'middle_name',
    'type' => 'xsd:string'
    ),
    'last_name' => array (
    'name' => 'last_name',
    'type' => 'xsd:string'
    ),
    'suffix_id' => array (
    'name' => 'suffix_id',
    'type' => 'xsd:string'
    ),
    'nick_name' => array (
    'name' => 'nick_name',
    'type' => 'xsd:string'
    ),
    'job_title' => array (
    'name' => 'job_title',
    'type' => 'xsd:string'
    ),
    'gender_id' => array (
    'name' => 'gender_id',
    'type' => 'xsd:string'
    ),
    'current_employer' => array (
    'name' => 'current_employer',
    'type' => 'xsd:string'
    ),
    'street_address' => array (
    'name' => 'street_address',
    'type' => 'xsd:string'
    ),
    'supplemental_address_1' => array (
    'name' => 'supplemental_address_1',
    'type' => 'xsd:string'
    ),
    'supplemental_address_2' => array (
    'name' => 'supplemental_address_2',
    'type' => 'xsd:string'
    ),
    'city' => array (
    'name' => 'city',
    'type' => 'xsd:string'
    ),
    'postal_code' => array (
    'name' => 'postal_code',
    'type' => 'xsd:string'
    ),
    'state_province_id' => array (
    'name' => 'state_province_id',
    'type' => 'xsd:string'
    ),
    'phone' => array (
    'name' => 'phone',
    'type' => 'xsd:string'
    ),
    'email' => array (
    'name' => 'email',
    'type' => 'xsd:string'
    ),
    'contact_id' => array (
    'name' => 'contact_id',
    'type' => 'xsd:string'
    )
));


$SoapServer->wsdl->addComplexType('ContactArray', 'complexType', 'array', '', 'SOAP-ENC:Array', array (), array (
    array (
    'ref' => 'SOAP-ENC:arrayType',
    'wsdl:arrayType' => 'tns:Contact[]'
    )
    ), 'tns:Contact');



// Output DistributionsLists = Groups and Tags in CiviCrm
$SoapServer->wsdl->addComplexType('DistributionList', 'complexType', 'struct', 'all', '', array (
    'type' => array (
    'name' => 'type',
    'type' => 'xsd:string'
    ),
    'id' => array (
    'name' => 'id',
    'type' => 'xsd:string'
    ),
    'title' => array (
    'name' => 'title',
    'type' => 'xsd:string'
    ),
    'recipients' => array (
    'name' => 'recipients',
    'type' => 'xsd:string'
    )
));

//Output DistributionListArray
$SoapServer->wsdl->addComplexType('DistributionListArray', 'complexType', 'array', '', 'SOAP-ENC:Array', array (), array (
    array (
    'ref' => 'SOAP-ENC:arrayType',
    'wsdl:arrayType' => 'tns:DistributionList[]'
    )
    ), 'tns:DistributionList');



// Output of the Count Call
$SoapServer->wsdl->addComplexType('ContactCountWithTimeStamp', 'complexType', 'struct', 'all', '', array (
    'count' => array (
    'name' => 'count',
    'type' => 'xsd:int'
    ),
    'time_stamp' => array (
    'name' => 'time_stamp',
    'type' => 'xsd:int'
    )
));


/* Function declartion  &  registering --> get_contacts
   Desc: For searching all contacts                    */
$SoapServer->register('get_DistListCount', array (), array (
    'return' => 'xsd:int'
    ), 'http://civicrm.org/civicrm');

function get_DistListCount() {
    $ContactsForOutlook = new ContactsForOutlook();
    $ContactCount = array();
    $retarr = $ContactsForOutlook->GetDistListCount();
    return (int)$retarr;
}


/* Function declartion  &  registering --> get_groups
   Desc: For searching all Groups                    */
$SoapServer->register('get_DistList', array (
    'params' => 'tns:Rows'
    ), array (
    'return' => 'tns:DistributionListArray'
    ), 'http://civicrm.org/civicrm');

function get_DistList($params) {
    $ContactsForOutlook = new ContactsForOutlook();
    $retarr = $ContactsForOutlook->GetDistList($params);
    return $retarr;
}

/* Function declartion  &  registering --> get_contacts
   Desc: For searching all contacts                    */
$SoapServer->register('get_contacts_count', array (
    'timestamp' => 'xsd:int'
    ), array (
    'return' => 'tns:ContactCountWithTimeStamp'
    ), 'http://civicrm.org/civicrm');

function get_contacts_count($timestamp) {
    $ContactsForOutlook = new ContactsForOutlook();
    $ContactCount = array();
    $retarr = $ContactsForOutlook->GetCount($timestamp);
    $ContactCount['count']= (int)$retarr;
    $ContactCount['time_stamp'] = (int)time();
    return $ContactCount;
}

/* Function declartion  &  registering --> get_contacts
   Desc: For searching all contacts                    */
$SoapServer->register('get_contacts', array (
    'params' => 'tns:Rows',
    'timestamp' => 'xsd:int'
    ), array (
    'return' => 'tns:ContactArray'
    ), 'http://civicrm.org/civicrm');

function get_contacts($params,$timestamp) {
    $ContactsForOutlook = new ContactsForOutlook();
    $retarr = $ContactsForOutlook->GetContacts($params,$timestamp);
    return $retarr;
}

// pass incoming (posted) data
$SoapServer->service($HTTP_RAW_POST_DATA);
?>
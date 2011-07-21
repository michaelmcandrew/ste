<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * File for the CiviCRM APIv3 Pledge functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Pledge
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Pledge.php
 *
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Utils/Rule.php';

/**
 * Add or update a plege
 *
 * @param  array   $params           (reference ) input parameters. Fields from interogate function should all work
 *
 * @return array (reference )        array representing created pledge
 * @static void
 * @access public
 */
function civicrm_api3_pledge_create( $params ) {
  _civicrm_api3_initialize(true );
  try{

    if ($params['pledge_amount']){
      //acceptable in unique format or DB format but change to unique format here
      $params['amount'] = $params['pledge_amount'];
    }
     $required =  array('contact_id', 'amount', array('pledge_contribution_type_id','contribution_type_id') , 'installments','start_date');
    
    if(CRM_Utils_Array::value('id',$params)){
      //todo move this into civicrm_api3_verify mandatory in some way - or civicrm_api
      $required =  array('id');
    }
   civicrm_api3_verify_mandatory ($params,null,$required);
     
    $values  = array( );
    require_once 'CRM/Pledge/BAO/Pledge.php';
    //check that fields are in appropriate format. Dates will be formatted (within reason) by this function
    $error = _civicrm_api3_pledge_format_params( $params, $values,TRUE ); 
    if ( civicrm_api3_error( $error ) ) {
        return $error;
    } 

    $pledge = CRM_Pledge_BAO_Pledge::create( $values );
   if ( is_a( $pledge, 'CRM_Core_Error' ) ) {
        return civicrm_api3_create_error(  $pledge->_errors[0]['message'] );
    }else{
         _civicrm_api3_object_to_array($pledge, $pledgeArray[$pledge->id]);

    }

    return civicrm_api3_create_success($pledgeArray,$params,$pledge);
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}

/**
 * Delete a pledge
 *
 * @param  array   $params           array included 'pledge_id' of pledge to delete
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 */
function civicrm_api3_pledge_delete( $params ) {
  _civicrm_api3_initialize(true);
  try{

    civicrm_api3_verify_one_mandatory ($params,null,array('id', 'pledge_id'));
    if (!empty($params['id'])){
      //handle field name or unique db name
      $params['pledge_id'] = $params['id'];
    }

    $pledgeID = CRM_Utils_Array::value( 'pledge_id', $params );
    if ( ! $pledgeID ) {
      return civicrm_api3_create_error(  'Could not find pledge_id in input parameters' );
    }

    require_once 'CRM/Pledge/BAO/Pledge.php';
    if ( CRM_Pledge_BAO_Pledge::deletePledge( $pledgeID ) ) {
      return civicrm_api3_create_success(array($pledgeID =>$pledgeID) );
    } else {
      return civicrm_api3_create_error(  'Could not delete pledge'  );
    }
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}

/**
 * Retrieve a set of pledges, given a set of input params
 *
 * @param  array   $params           (reference ) input parameters. Use interogate for possible fields
 *
 * @return array (reference )        array of pledges, if error an array with an error id and error message
 * @static void
 * @access public
 */
function civicrm_api3_pledge_get( $params ) {
  _civicrm_api3_initialize(true );
  try{
    civicrm_api3_verify_mandatory ($params);
    if(!empty($params['id'])  && empty($params['pledge_id'])){
      //if you pass in 'id' it will be treated by the query as contact_id
      $params['pledge_id'] = $params['id'];
      unset ($params['id']);
    }
    $inputParams      = array( );
    $returnProperties = array( );
    $otherVars = array( 'sort', 'offset', 'rowCount' );

    $sort     = null;
    $offset   = 0;
    $rowCount = 25;
    foreach ( $params as $n => $v ) {
      if ( substr( $n, 0, 7 ) == 'return.' ) {
        $returnProperties[ substr( $n, 7 ) ] = $v;
      } elseif ( in_array( $n, $otherVars ) ) {
        $$n = $v;
      } else {
        $inputParams[$n] = $v;
      }
    }

    // add is_test to the clause if not present
    if ( ! array_key_exists( 'pledge_test', $inputParams ) ) {
      $inputParams['pledge_test'] = 0;
    }

    require_once 'CRM/Pledge/BAO/Query.php';
    require_once 'CRM/Contact/BAO/Query.php';
    if ( empty( $returnProperties ) ) {
      $returnProperties = CRM_Pledge_BAO_Query::defaultReturnProperties( CRM_Contact_BAO_Query::MODE_PLEDGE );
    }else{
      $returnProperties['pledge_id']=1;
    }

    $newParams =& CRM_Contact_BAO_Query::convertFormValues( $inputParams );

    $query = new CRM_Contact_BAO_Query( $newParams, $returnProperties, null,
                                        false, false, CRM_Contact_BAO_Query::MODE_PLEDGE );
    list( $select, $from, $where ) = $query->query( );

    $sql = "$select $from $where";

    if ( ! empty( $sort ) ) {
      $sql .= " ORDER BY $sort ";
    }
    $sql .= " LIMIT $offset, $rowCount ";
    $dao =& CRM_Core_DAO::executeQuery( $sql );

    $pledge = array( );
    while ( $dao->fetch( ) ) {
      if ($params['sequential']){
        $pledge[] = $query->store( $dao );
      }else{
        $pledge[$dao->pledge_id] = $query->store( $dao );
      }
    }

    return civicrm_api3_create_success($pledge,$params,$dao);
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}

/**
 * take the input parameter list as specified in the data model and
 * convert it into the same format that we use in QF and BAO object
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new contact.
 * @param array  $values       The reformatted properties that we can use internally
 *                            '
 * @return array|CRM_Error
 * @access public
 */
function _civicrm_api3_pledge_format_params( $params, &$values, $create=false ) {
  // based on contribution apis - copy all the pledge fields - this function filters out non -valid fields but unfortunately
  // means we have to put them back where there are 2 names for the field (name in table & unique name)
  // since there is no clear std to use one or the other. Generally either works ? but not for create date
  // perhaps we should just copy $params across rather than run it through the 'filter'?
  // but at least the filter forces anomalies into the open. In several cases it turned out the unique names wouldn't work
  // even though they are 'generally' what is returned in the GET - implying they should
  $fields =& CRM_Pledge_DAO_Pledge::fields( );
  _civicrm_api3_store_values( $fields, $params, $values );


  //add back the fields we know of that got dropped by the previous function
  if (!empty($params['pledge_create_date'])){
    //pledge_create_date will not be formatted by the format params function so change back to create_date
    $values['create_date'] = $params['pledge_create_date'];
  }else{

    //create_date may have been dropped by the $fields function so retrieve it
    $values['create_date'] = CRM_Utils_Array::value('create_date',$params);
  }

    //field has been renamed - don't lose it! Note that this must be called
    // installment amount not pledge_installment_amount, pledge_original_installment_amount
    // or original_installment_amount to avoid error
    // Division by zero in CRM\Pledge\BAO\Payment.php:162
    // but we should accept the variant because they are all 'logical assumptions' based on the
    // 'standards'
    $values['installment_amount'] = CRM_Utils_Array::value('installment_amount',$params);

  
  if ( array_key_exists( 'original_installment_amount', $params ) ) {
    $values['installment_amount'] = $params['original_installment_amount'];
    //it seems it will only create correctly with BOTH installment amount AND pledge_installment_amount set
    //pledge installment amount required for pledge payments
    $values['pledge_original_installment_amount'] = $params['original_installment_amount'];
  }

  if ( array_key_exists( 'pledge_original_installment_amount', $params ) ) {
    $values['installment_amount'] = $params['pledge_original_installment_amount'];
  }
 
  if ( array_key_exists( 'status_id', $params ) ){
    $values['pledge_status_id'] = $params['status_id'];
  }
  if ( array_key_exists('contact_id',$params)){
    //this is validity checked further down to make sure the contact exists
    $values['pledge_contact_id'] = $params['contact_id'];
  }
  if ( array_key_exists( 'id', $params )  ){
    //retrieve the id key dropped from params. Note we can't use pledge_id because it
    //causes an error in CRM_Pledge_BAO_Payment - approx line 302
    $values['id'] = $params['id'];
  }
  if ( array_key_exists( 'pledge_id', $params )  ){
    //retrieve the id key dropped from params. Note we can't use pledge_id because it
    //causes an error in CRM_Pledge_BAO_Payment - approx line 302
    $values['id'] = $params['pledge_id'];
    unset($values['pledge_id']);
  }
  if ( array_key_exists( 'status_id', $params ) ){
    $values['pledge_status_id'] = $params['status_id'];
  }
  if ( empty( $values['status_id'] ) ){
    $values['status_id'] = $values['pledge_status_id'];
  }
  if (empty($values['id'])){
    //at this point both should be the same so unset both if not set - passing in empty
    //value causes crash rather creating new - do it before next section as null values ignored in 'switch'
    unset($values['id']);
  }
  if ( !empty( $params['scheduled_date']) ){
    //scheduled date is required to set next payment date - defaults to start date
    $values['scheduled_date'] = $params['scheduled_date'];
  }elseif (array_key_exists( 'start_date', $params )){
    $values['scheduled_date'] = $params['start_date'];
  }

  foreach ($values as $key => $value) {
    // ignore empty values or empty arrays etc
    if ( CRM_Utils_System::isNull( $value ) ) {
      continue;
    }
    switch ($key) {

      case 'pledge_contact_id':
        if (!CRM_Utils_Rule::integer($value)) {
          return civicrm_api3_create_error("contact_id not valid: $value");
        }
        $dao = new CRM_Core_DAO();
        $qParams = array();
        $svq = $dao->singleValueQuery("SELECT id FROM civicrm_contact WHERE id = $value",
        $qParams);
        if (!$svq) {
          return civicrm_api3_create_error("Invalid Contact ID: There is no contact record with contact_id = $value.");
        }

        $values['contact_id'] = $values['pledge_contact_id'];
        unset ($values['pledge_contact_id']);
        break;
      case 'pledge_id':
        if (!CRM_Utils_Rule::integer($value)) {
          return civicrm_api3_create_error("contact_id not valid: $value");
        }
        $dao = new CRM_Core_DAO();
        $qParams = array();
        $svq = $dao->singleValueQuery("SELECT id FROM civicrm_pledge WHERE id = $value",
        $qParams);
        if (!$svq) {
          return civicrm_api3_create_error("Invalid Contact ID: There is no contact record with contact_id = $value.");
        }

        break;


      case 'create_date':
      case 'scheduled_date':
      case 'start_date':
        if (!CRM_Utils_Rule::datetime($value)) {
          return civicrm_api3_create_error("$key not a valid date: $value");
        }
        break;
      case 'installment_amount':
      case 'amount':
        if (!CRM_Utils_Rule::money($value)) {
          return civicrm_api3_create_error("$key not a valid amount: $value");
        }
        break;
      case 'currency':
        if (!CRM_Utils_Rule::currencyCode($value)) {
          return civicrm_api3_create_error("currency not a valid code: $value");
        }
        break;
      default:
        break;
    }
  }

  //format the parameters
  _civicrm_api3_custom_format_params( $params, $values, 'Pledge' );


  return array();
}



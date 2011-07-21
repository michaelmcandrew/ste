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
 * File for the CiviCRM APIv3 membership status functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Membership
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: MembershipStatus.php 30171 2010-10-14 09:11:27Z mover $
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Member/BAO/MembershipStatus.php';
/**
 * Create a Membership Status
 *
 * This API is used for creating a Membership Status
 *
 * @param   array  $params  an associative array of name/value property values of civicrm_membership_status
 * @return array of newly created membership status property values.
 * @access public
 */
function civicrm_api3_membership_status_create($params) {
	_civicrm_api3_initialize ( true );
	try {
		
		civicrm_api3_verify_one_mandatory ( $params, 'CRM_Member_DAO_MembershipStatus', array ('name', 'label' ) );
		//move before verifiy? DAO check requires?
		if (empty ( $params ['name'] )) {
			$params ['name'] = CRM_Utils_Array::value ( 'label', $params );
		}
		
		//don't allow duplicate names.
		require_once 'CRM/Member/DAO/MembershipStatus.php';
		$status = new CRM_Member_DAO_MembershipStatus ();
		$status->name = $params ['name'];
		if ($status->find ( true )) {
			return civicrm_api3_create_error ( ts ( 'A membership status with this name already exists.' ) );
		}
		
		require_once 'CRM/Member/BAO/MembershipStatus.php';
		$ids = array ();
		$membershipStatusBAO = CRM_Member_BAO_MembershipStatus::add ( $params, $ids );
		if (is_a ( $membershipStatusBAO, 'CRM_Core_Error' )) {
			return civicrm_api3_create_error ( "Membership is not created" );
		} else {
			$values = array ();
			$values ['id'] = $membershipStatusBAO->id;
			$values ['is_error'] = 0;
			return civicrm_api3_create_success ( $values, $params );
		}
	} catch ( PEAR_Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	} catch ( Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	}
}

/**
 * Get a membership status.
 *
 * This api is used for finding an existing membership status.
 *
 * @param  array $params  an associative array of name/value property values of civicrm_membership_status
 *
 * @return  Array of all found membership status property values.
 * @access public
 */
function civicrm_api3_membership_status_get($params) {

	try {
		civicrm_api3_verify_mandatory ( $params );

    return _civicrm_api3_basic_get('CRM_Member_BAO_MembershipStatus', $params);		

	} catch ( PEAR_Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	} catch ( Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	}
}

/**
 * Update an existing membership status
 *
 * This api is used for updating an existing membership status.
 * Required parrmeters : id of a membership status
 *
 * @param  Array   $params  an associative array of name/value property values of civicrm_membership_status
 *
 * @return array of updated membership status property values
 * @access public
 */
function &civicrm_api3_membership_status_update($params) {
	_civicrm_api3_initialize ();
	if (! is_array ( $params )) {
		return civicrm_api3_create_error ( 'Input variable `params` is not an array' );
	}
	
	if (! isset ( $params ['id'] )) {
		return civicrm_api3_create_error ( 'Required parameter missing' );
	}
	
	//don't allow duplicate names.
	$name = CRM_Utils_Array::value ( 'name', $params );
	if ($name) {
		require_once 'CRM/Member/DAO/MembershipStatus.php';
		$status = new CRM_Member_DAO_MembershipStatus ();
		$status->name = $params ['name'];
		if ($status->find ( true ) && $status->id != $params ['id']) {
			return civicrm_api3_create_error ( ts ( 'A membership status with this name already exists.' ) );
		}
	}
	
	require_once 'CRM/Member/BAO/MembershipStatus.php';
	$membershipStatusBAO = new CRM_Member_BAO_MembershipStatus ();
	$membershipStatusBAO->id = $params ['id'];
	if ($membershipStatusBAO->find ( true )) {
		$fields = $membershipStatusBAO->fields ();
		foreach ( $fields as $name => $field ) {
			if (array_key_exists ( $name, $params )) {
				$membershipStatusBAO->$name = $params [$name];
			}
		}
		$membershipStatusBAO->save ();
	}
	$membershipStatus = array ();
	_civicrm_api3_object_to_array ( clone ($membershipStatusBAO), $membershipStatus );
	$membershipStatus ['is_error'] = 0;
	return $membershipStatus;
}

/**
 * Deletes an existing membership status
 *
 * This API is used for deleting a membership status
 *
 * @param  Int  $membershipStatusID   Id of the membership status to be deleted
 *
 * @return null if successfull, object of CRM_Core_Error otherwise
 * @access public
 */
function civicrm_api3_membership_status_delete($params) {
	_civicrm_api3_initialize ( true );
	try {
		civicrm_api3_verify_mandatory ( $params, null, array ('id' ) );
		require_once 'CRM/Member/BAO/MembershipStatus.php';
		$memberStatusDelete = CRM_Member_BAO_MembershipStatus::del ( $params ['id'], true );
		return $memberStatusDelete ? civicrm_api3_create_error ( 'Error while deleting membership type Status' ) : civicrm_api3_create_success ();
	} catch ( PEAR_Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	} catch ( Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	}
}

/**
 * Derives the Membership Status of a given Membership Reocrd
 *
 * This API is used for deriving Membership Status of a given Membership
 * record using the rules encoded in the membership_status table.
 *
 * @param  Int     $membershipID  Id of a membership
 * @param  String  $statusDate
 *
 * @return Array  Array of status id and status name
 * @public
 */
function civicrm_api3_membership_status_calc($membershipParams) {
	if (! is_array ( $membershipParams )) {
		return civicrm_api3_create_error ( ts ( 'Input variable `params` is not an array' ) );
	}
	
	if (! ($membershipID = CRM_Utils_Array::value ( 'membership_id', $membershipParams ))) {
		return civicrm_api3_create_error ( 'membershipParams do not contain membership_id' );
	}
	
	$query = "
SELECT start_date, end_date, join_date
  FROM civicrm_membership
 WHERE id = %1
";
	$params = array (1 => array ($membershipID, 'Integer' ) );
	$dao = & CRM_Core_DAO::executeQuery ( $query, $params );
	if ($dao->fetch ()) {
		require_once 'CRM/Member/BAO/MembershipStatus.php';
		$result = &
    CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate ( $dao->start_date, $dao->end_date, $dao->join_date );
		
		//make is error zero only when valid status found.
		if (CRM_Utils_Array::value ( 'id', $result )) {
			$result ['is_error'] = 0;
		}
	} else {
		$result = civicrm_api3_create_error ( 'did not find a membership record' );
	}
	$dao->free ();
	return $result;
}
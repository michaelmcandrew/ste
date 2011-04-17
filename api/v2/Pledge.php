<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * File for the CiviCRM APIv2 Pledge functions
 *
 * @package CiviCRM_APIv2
 * @subpackage API_Pledge
 *
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Pledge.php 
 *
 */

/**
 * Include utility functions
 */
require_once 'api/v2/utils.php';
require_once 'CRM/Utils/Rule.php';

/**
 * Add or update a plege
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return array (reference )        pledge_id of created or updated record
 * @static void
 * @access public
 */
function &civicrm_pledge_add( &$params ) {
    _civicrm_initialize( );

    if ( empty( $params ) ) {
        return civicrm_create_error( ts( 'No input parameters present' ) );
    }

    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Input parameters is not an array' ) );
    }

    $error = _civicrm_pledge_check_params( $params );
    if ( civicrm_error( $error ) ) {
        return $error;
    }

    $values  = array( );
   
    require_once 'CRM/Pledge/BAO/Pledge.php';
    $error = _civicrm_pledge_format_params( $params, $values );
    if ( civicrm_error( $error ) ) {
        return $error;
    }

    $values["contact_id"] = $params["contact_id"];
    $values["source"]     = $params["source"];
    
    $ids     = array( );
    if ( CRM_Utils_Array::value( 'id', $params ) ) {
        $ids['pledge'] = $params['id'];
    }
    $pledge = CRM_Pledge_BAO_Pledge::create( $values, $ids );
    if ( is_a( $pledge, 'CRM_Core_Error' ) ) {
        return civicrm_create_error( ts( $pledge->_errors[0]['message'] ) );
    }

    _civicrm_object_to_array($pledge, $pledgeArray);
    
    return $pledgeArray;
}

/**
 * Retrieve a specific pledge, given a set of input params
 * If more than one pledge exists, return an error, unless
 * the client has requested to return the first found contact
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return array (reference )        array of properties, if error an array with an error id and error message
 * @static void
 * @access public
 */
function &civicrm_pledge_get( &$params ) {
    _civicrm_initialize( );

    $values = array( );
    if ( empty( $params ) ) {
        return civicrm_create_error( ts( 'No input parameters present' ) );
    }
    
    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Input parameters is not an array' ) );
    }

    $pledges =& civicrm_pledge_search( $params );
    if ( civicrm_error( $pledges ) ) {
        return $pledges;
    }

    if ( count( $pledges ) != 1 &&
         ! $params['returnFirst'] ) {
        return civicrm_create_error( ts( '%1 pledges matching input params', array( 1 => count( $pledges ) ) ),
                                     $pledges );
    }

    $pledges = array_values( $pledges );
    return $pledges[0];
}

/**
 * Delete a pledge
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 */
function civicrm_pledge_delete( &$params ) {

    $pledgeID = CRM_Utils_Array::value( 'pledge_id', $params );
    if ( ! $pledgeID ) {
        return civicrm_create_error( ts( 'Could not find pledge_id in input parameters' ) );
    }

    require_once 'CRM/Pledge/BAO/Pledge.php';
    if ( CRM_Pledge_BAO_Pledge::deletePledge( $pledgeID ) ) {
        return civicrm_create_success( );
    } else {
        return civicrm_create_error( ts( 'Could not delete pledge' ) );
    }
}

/**
 * Retrieve a set of pledges, given a set of input params
 *
 * @param  array   $params           (reference ) input parameters
 * @param array    $returnProperties Which properties should be included in the
 *                                   returned pledge object. If NULL, the default
 *                                   set of properties will be included.
 *
 * @return array (reference )        array of pledges, if error an array with an error id and error message
 * @static void
 * @access public
 */
function &civicrm_pledge_search( &$params ) {
    _civicrm_initialize( );

    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Input parameters is not an array' ) );
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
    }
    
    $newParams =& CRM_Contact_BAO_Query::convertFormValues( $inputParams );

    $query = new CRM_Contact_BAO_Query( $newParams, $returnProperties, null );
    list( $select, $from, $where ) = $query->query( );
    
    $sql = "$select $from $where";  

    if ( ! empty( $sort ) ) {
        $sql .= " ORDER BY $sort ";
    }
    $sql .= " LIMIT $offset, $rowCount ";
    $dao =& CRM_Core_DAO::executeQuery( $sql );
    
    $pledge = array( );
    while ( $dao->fetch( ) ) {
        $pledge[$dao->pledge_id] = $query->store( $dao );
    }
    $dao->free( );
    
    return $pledge;
}

/**
 *
 * @param <type> $params
 * @return <type> 
 */
function &civicrm_pledge_format_create( &$params ) {
    _civicrm_initialize( );
   
    // return error if we have no params
    if ( empty( $params ) ) {
        return civicrm_create_error( 'Input Parameters empty' );
    }
    
    $error = _civicrm_pledge_check_params($params);
    if ( civicrm_error( $error ) ) {
        return $error;
    }
    $values  = array( );
    $error = _civicrm_pledge_format_params($params, $values);
    if ( civicrm_error( $error ) ) {
        return $error;
    }
    
    $error = _civicrm_pledge_duplicate_check($params);
    if ( civicrm_error( $error ) ) {
        return $error;
    }
    $ids = array();
    
    CRM_Pledge_BAO_Pledge::resolveDefaults($params, true);

    $pledge = CRM_Pledge_BAO_Pledge::create( $params, $ids );
    _civicrm_object_to_array($pledge, $pledgeArray);
    return $pledgeArray;

}

/**
 * This function ensures that we have the right input pledge parameters
 *
 * We also need to make sure we run all the form rules on the params list
 * to ensure that the params are valid
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new pledge.
 *
 * @return bool|CRM_Utils_Error
 * @access private
 */
function _civicrm_pledge_check_params( &$params ) {
    static $required = array( 'contact_id', 'amount', 'contribution_type_id' );
    
    // cannot create a pledge with empty params
    if ( empty( $params ) ) {
        return civicrm_create_error( 'Input Parameters empty' );
    }

    $valid = true;
    $error = '';
    foreach ( $required as $field ) {
        if ( ! CRM_Utils_Array::value( $field, $params ) ) {
            $valid = false;
            $error .= $field;
            break;
        }
    }
    
    if ( ! $valid ) {
        return civicrm_create_error( "Required fields not found for pledge $error" );
    }
    
    return array();
}

/**
 * Check if there is a pledge with the same trxn_id or invoice_id
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new pledge.
 *
 * @return array|CRM_Error
 * @access private
 */
function _civicrm_pledge_duplicate_check( &$params ) {
    require_once 'CRM/Pledge/BAO/Pledge.php';
    $duplicates = array( );
    $result = CRM_Pledge_BAO_Pledge::checkDuplicate( $params,$duplicates ); 
    if ( $result ) {
        $d = implode( ', ', $duplicates );
        $error = CRM_Core_Error::createError( "Duplicate error - existing pledge record(s) have a matching Transaction ID or Invoice ID. pledge record ID(s) are: $d", CRM_Core_Error::DUPLICATE_pledge, 'Fatal', $d);
        return civicrm_create_error( $error->pop( ),
                                     $d );
    } else {
        return array();
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
function _civicrm_pledge_format_params( &$params, &$values, $create=false ) {
    // copy all the pledge fields as is
   
    $fields =& CRM_Pledge_DAO_Pledge::fields( );

    _civicrm_store_values( $fields, $params, $values );

    foreach ($params as $key => $value) {
        // ignore empty values or empty arrays etc
        if ( CRM_Utils_System::isNull( $value ) ) {
            continue;
        }

        switch ($key) {

        case 'pledge_contact_id':
            if (!CRM_Utils_Rule::integer($value)) {
                return civicrm_create_error("contact_id not valid: $value");
            }
            $dao = new CRM_Core_DAO();
            $qParams = array();
            $svq = $dao->singleValueQuery("SELECT id FROM civicrm_contact WHERE id = $value",
                                          $qParams);
            if (!$svq) {
                return civicrm_create_error("Invalid Contact ID: There is no contact record with contact_id = $value.");
            }
            
            $values['contact_id'] = $values['pledge_contact_id'];
            unset ($values['pledge_contact_id']);
            break;

        case 'receive_date':
        case 'cancel_date':
        case 'receipt_date':
        case 'thankyou_date':
            if (!CRM_Utils_Rule::date($value)) {
                return civicrm_create_error("$key not a valid date: $value");
            }
            break;

        case 'non_deductible_amount':
        case 'total_amount':
        case 'fee_amount':
        case 'net_amount':
            if (!CRM_Utils_Rule::money($value)) {
                return civicrm_create_error("$key not a valid amount: $value");
            }
            break;
        case 'currency':
            if (!CRM_Utils_Rule::currencyCode($value)) {
                return civicrm_create_error("currency not a valid code: $value");
            }
            break;
        case 'pledge_type':            
            $values['pledge_type_id'] = CRM_Utils_Array::key( ucfirst( $value ),
                                                                    CRM_Pledge_PseudoConstant::pledgeType( )
                                                                    );
            break;
        case 'payment_instrument': 
            require_once 'CRM/Core/OptionGroup.php';
            $values['payment_instrument_id'] = CRM_Core_OptionGroup::getValue( 'payment_instrument', $value );
            break;
        default:
            break;
        }
    }

    if ( array_key_exists( 'note', $params ) ) {
        $values['note'] = $params['note'];
    }

    _civicrm_custom_format_params( $params, $values, 'Pledge' );
    
    if ( $create ) {
        // CRM_pledge_BAO_Pledge::add() handles Pledge_source
        // So, if $values contains Pledge_source, convert it to source
        $changes = array( 'pledge_source' => 'source' );
        
        foreach ($changes as $orgVal => $changeVal) {
            if ( isset($values[$orgVal]) ) {
                $values[$changeVal] = $values[$orgVal];
                unset($values[$orgVal]);
            }
        }
    }
    
    return array();
}

/**
 * Process a transaction and record it against the contact.
 * 
 * @param  array   $params           (reference ) input parameters
 *
 * @return array (reference )        pledge of created or updated record (or a civicrm error)
 * @static void
 * @access public
 * 
 */
function civicrm_pledge_transact($params) {
  civicrm_initialize( );

  if ( empty( $params ) ) {
    return civicrm_create_error( ts( 'No input parameters present' ) );
  }
  
  if ( ! is_array( $params ) ) {
    return civicrm_create_error( ts( 'Input parameters is not an array' ) );
  }
  
  $values  = array( );

  require_once 'CRM/Pledge/BAO/Pledge.php';
  $error = _civicrm_pledge_format_params( $params, $values );
  if ( civicrm_error( $error ) ) {
    return $error;
  }

  $required = array( 
    'amount', 
  ) ;
  foreach ( $required as $key ) {
    if ( !isset($params[$key]) ) {
      return civicrm_create_error("Missing parameter $key: civicrm_pledge_transact() requires a parameter '$key'.");
    }
  }

  // allow people to omit some values for convenience
  $defaults = array(
    // 'payment_processor_id' => NULL /* we could retrieve the default processor here, but only if it's missing to avoid an extra lookup */
    'payment_processor_mode' => 'live',
  ) ;
  $params = array_merge($defaults, $params) ;

  // clean up / adjust some values which 
  if ( !isset($params['total_amount']) ) {
    $params['total_amount'] = $params['amount'] ;
  }
  if ( !isset($params['net_amount']) ) {
    $params['net_amount'] = $params['amount'] ;
  }
  if ( !isset($params['receive_date']) ) {
    $params['receive_date'] = date('Y-m-d');
  }
  if ( !isset($params['invoiceID']) && isset($params['invoice_id']) ) {
      $params['invoiceID'] = $params['invoice_id'] ;
  }

  require_once 'CRM/Core/BAO/PaymentProcessor.php';
  $paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment( $params['payment_processor_id'],
                                                                 $params['payment_processor_mode'] );
  if ( civicrm_error($paymentProcessor) ) {
    return $paymentProcessor ;
  }

  require_once 'CRM/Core/Payment.php';
  $payment =& CRM_Core_Payment::singleton( $params['payment_processor_mode'], 'Pledge', $paymentProcessor );
  if ( civicrm_error($payment) ) {
    return $payment ;
  }

  $transaction = $payment->doDirectPayment($params);
  if ( civicrm_error($transaction) ) {
    return $transaction ;
  }

  // but actually, $payment->doDirectPayment() doesn't return a
  // CRM_Core_Error by itself
  if ( get_class($transaction) == 'CRM_Core_Error' ) {
      $errs = $transaction->getErrors() ;
      if ( !empty($errs) ) {
          $last_error = array_shift($errs) ;
          return CRM_Core_Error::createApiError($last_error['message']);
      }
  }

  $pledge = civicrm_pledge_add($params);
  return $pledge ;
}


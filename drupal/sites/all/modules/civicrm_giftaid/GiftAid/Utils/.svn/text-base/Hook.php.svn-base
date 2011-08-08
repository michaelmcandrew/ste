<?php 

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
 *
 * @package CiviCRM_Hook
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id: $
 *
 */

class GiftAid_Utils_Hook {

    /**
     * This hook allows filtering contributions for gift-aid
     * @param bool    $isEligible eligibilty already detected if getDeclaration() method.
     * @param integer $contactID  contact being checked 
     * @param date    $date  date gift-aid declaration was made on 
     * @param $contributionID  contribution id if any being referred  
     *
     * @access public
     */
    static function giftAidEligible( &$isEligible, $contactID, $date = null, $contributionID = null ) {
        $config =& CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userHookClass ) . '.php' );
        $null =& CRM_Core_DAO::$_nullObject;

        return
            eval( 'return ' .
                  $config->userHookClass .
                  '::invoke( 4, $isEligible, $contactID, $date, $contributionID, $null , \'civicrm_giftAidEligible\' );' );
    }

    /**
     * This hook allows doing any extra processing for contributions that are added to a batch.
     * @param $contributionsAdded  contribution ids that have been batched  
     *
     * @access public
     */
    static function batchContributions( $batchID, $contributionsAdded ) {
        $config =& CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userHookClass ) . '.php' );
        $null =& CRM_Core_DAO::$_nullObject;

        return
            eval( 'return ' .
                  $config->userHookClass .
                  '::invoke( 2, $batchID, $contributionsAdded, $null, $null, $null , \'civicrm_batchContributions\' );' );
    }

    /**
     * This hook allows altering getDeclaration() query
     * @param string $query  declaration query
     * @param array  $queryParams  params required by query
     *
     * @access public
     */
    static function alterDeclarationQuery( &$query, &$queryParams ) {
        $config =& CRM_Core_Config::singleton( );
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $config->userHookClass ) . '.php' );
        $null =& CRM_Core_DAO::$_nullObject;

        return
            eval( 'return ' .
                  $config->userHookClass .
                  '::invoke( 2, $query, $queryParams, $null, $null, $null , \'civicrm_alterDeclarationQuery\' );' );
    }
}

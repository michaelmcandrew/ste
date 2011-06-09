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

function run( ) {
    session_start( );                               
                                            
    require_once '../../civicrm.config.php'; 
    require_once 'CRM/Core/Config.php'; 
    
    $config =& CRM_Core_Config::singleton(); 

    // this does not return on failure
    CRM_Utils_System::authenticateScript( true );

    require_once 'CRM/Core/BAO/Setting.php';
    $moveStatus = CRM_Core_BAO_Setting::doSiteMove( );

    echo $moveStatus . '<br />';
    echo ts("If no errors are displayed above, the site move steps have completed successfully. Please visit <a href=\"{$config->userFrameworkBaseURL}\">your moved site</a> and test the move.");
}

run( );



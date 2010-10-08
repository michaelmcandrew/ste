<?php
 
/*
 +--------------------------------------------------------------------+
 | Barclay Core Payment Module for CiviCRM version 3.1           |
 +--------------------------------------------------------------------+
 | Licensed to CiviCRM under the Academic Free License version 3.0    |
 |                                                                    |
 | Written & Contributed by Eileen McNaughton    |
 +--------------------------------------------------------------------+
 |                                                                    |                          |
 |                                                                    |
 | This module is based very much on the eWay module    |
                        |
 |                                                                    |
 +--------------------------------------------------------------------+
*/

require_once 'CRM/Core/Payment/Barclay.php';

class CRM_Event_Payment_Barclay extends CRM_Core_Payment_Barclay 
{        
   /** 
    * We only need one instance of this object. So we use the singleton 
    * pattern and cache the instance in this variable 
    * 
    * @var object 
    * @static 
    */ 
   static private $_singleton = null; 

   /** 
    * Constructor 
    *
    * @param string $mode the mode of operation: live or test
    * 
    * @return void 
    */ 
   function __construct( $mode, &$paymentProcessor ) 
   {
       parent::__construct( $mode, $paymentProcessor );
   }

   /** 
    * singleton function used to manage this object 
    * 
    * @param string $mode the mode of operation: live or test 
    * @return object 
    * @static 
    * 
    */ 
   static function &singleton( $mode, &$paymentProcessor ) 
   {
      if (self::$_singleton === null ) 
      { 
         self::$_singleton =& new CRM_Event_Payment_Barclay( $mode, $paymentProcessor );
      } 
      return self::$_singleton; 
   } 

} // end class CRM_Event_Payment_Barclay


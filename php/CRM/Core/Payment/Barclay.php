<?php

/*
 +----------------------------------------------------------------------------+
 | Barclay  Core Payment Module for CiviCRM version 3.1 |
 +----------------------------------------------------------------------------+
 | Licensed to CiviCRM under the Academic Free License version 3.0            |
 |                                                                            |
 | Written & Contributed by Eileen McNaughton                |
 +----------------------------------------------------------------------------+
*/

/*
-----------------------------------------------------------------------------------------------
The basic functionality of this processor is that variables from the $params object are transformed
into xml. The xml is submitted to the processor's https site
using curl and the response is translated back into an array using the processor's function.

If an array ($params) is returned to the calling function the values from
the array are merged into the calling functions array.

If an result of class error is returned it is treated as a failure. No error denotes a success. Be
WARY of this when coding

 -----------------------------------------------------------------------------------------------
**/


require_once 'CRM/Core/Payment.php';

class CRM_Core_Payment_Barclay extends CRM_Core_Payment
{
    const
        CHARSET  = 'UFT-8'; // (not used, implicit in the API, might need to convert?)

    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     *
     * @var object
     * @static
     */
    static private $_singleton = null;

    /**********************************************************
     * Constructor
     *
     * @param string $mode the mode of operation: live or test
     *
     * @return void
     **********************************************************/

    function __construct( $mode, &$paymentProcessor )
    {
        $this->_mode             = $mode;   // live or test
        $this->_paymentProcessor = $paymentProcessor;
        $this->_processorName    = ts('Barclay');
    }

    /**********************************************************
     * This function is set up and put here to make the mapping of fields
     * from the params object  as visually clear as possible for easy editing
     *
     *  Comment out irrelevant fields
     **********************************************************/
    function mapProcessorFieldstoParams($params)
    {	
        /**********************************************************
         * compile array. This array follows the structure of the xml 
         * it will be converted to. XML root for this purpose is 'EngineDoc'
         * Payment Processor field name fields from $params array
         **********************************************************/	
        $requestFields['CardholderPresentCode'] = 7 ; // review this for recurring or 3d-Secure
        $requestFields['User']['Name'] = $this->_paymentProcessor['user_name'];
        $requestFields['User']['Password']	  								= $this->_paymentProcessor['password'];
        $requestFields['User']['ClientId']	  								= $this->_paymentProcessor['signature'];
		$requestFields['Instructions']['Pipeline'] 				= 'PaymentNoFraud';//use 'payment' if using 3d-Secure
        $requestFields['OrderFormDoc']['Consumer']['BillTo']['FirstName']	= $params['billing_first_name'];//credit card name
        $requestFields['OrderFormDoc']['Consumer']['BillTo']['LastName']	= $params['billing_last_name'];//credit card name
        $requestFields['OrderFormDoc']['Consumer']['ShipTo']['FirstName']  	= $params['first_name'];//contact name
        $requestFields['OrderFormDoc']['Consumer']['ShipTo']['LastName']	= $params['last_name'];//contact name
        $requestFields['OrderFormDoc']['Consumer']['PaymentMech']['CreditCard']['Number']= $params['credit_card_number'];
        $requestFields['OrderFormDoc']['Consumer']['PaymentMech']['CreditCard']['Expires']	= sprintf('%02d', (int) $params['month'])."/".substr ($params['year'], 2, 2);;
        $requestFields['OrderFormDoc']['Consumer']['PaymentMech']['CreditCard']['Cvv2Val']	= $params[ 'cvv2'];
        $requestFields['OrderFormDoc']['Consumer']['PaymentMech']['CreditCard']['Cvv2Indicator']     = "1";    //CVV field passed to processor
        $requestFields['OrderFormDoc']['Consumer']['BillTo']['Street1']	     	= $params['street_address'];
        $requestFields['OrderFormDoc']['Consumer']['BillTo']['City']		    = $params['city'];
        $requestFields['OrderFormDoc']['Consumer']['BillTo']['State']		    = $params['state_province'];
        $requestFields['OrderFormDoc']['Consumer']['BillTo']['PostalCode']		= $params['postal_code'];
        $requestFields['OrderFormDoc']['Consumer']['BillTo']['Country']		    = $params['country'];//numeric codes - check this
        $requestFields['OrderFormDoc']['Consumer']['Email']		     			= $params['email'];
        $requestFields['OrderFormDoc']['Id']	     							= $params['invoiceID'];//32 character string
        $requestFields['OrderFormDoc']['Transaction']['Type']       			= 'Auth'; // (Auth is authorise & settle @ the same time)
        $requestFields['OrderFormDoc']['Transaction']['CurrentTotals']['Totals']['Total']	= ($params['amount'])*100 ;
        $requestFields['OrderFormDoc']['Mode'] 		  							= 'P';//for testing you can set this to 'Y' to get a success, 'N' for a failure, 'R' for random and 'FY' or 'FN' for results based on fraud settings

/*        FIELDS not being used at this stage but adding comments / documentation
*        $requestFields['Transaction']['PayerSecurityLevel'] - for 3D secure
*		 $requestFields['Transaction']['PayerAuthenticationCode']	- for 3D secure
*		 $requestFields['Transaction']['PayerTxnId'] 	- for 3D secure
         $requestFields['PaymentMech']['CreditCard']['StartDate'] - for UK Maestro / Solo - not currently collected in CiviCRM	
*        $requestFields['PaymentMech']['CreditCard']['Type'] = 	 $params['credit_card_type'];
// 		not set at this stage as appears optional (codes don't match Civi)
//       - the following is from Barclays doc :
// 		The type of credit card the consumer uses to pay for the
//		order. If this value is not supported, the ClearCommerce
//		Engine determines the type based on the credit card
// 		number.
//1 - Visa
//2 - MasterCard
//3 - Discover
//4 - Diners Club
//5 - Carte Blanche
//6 - JCB/JCL
//7 - enRoute
//8 - American Express
//9 - Solo
//10 - UK Maestro
//11 - Electron
//12- Deprecated
//13 - Reserved
//14 - Maestro
//15 - Reserved
//16 - Bill Me Later        
        /************************************************************************************
         *  Fields available from civiCRM not implemented for Barclay
         *
         *  $params['qfKey'];
         *  $params['amount_other'];
         *  $params['ip_address'];
         *  $params['contributionType_name'	];
         *  $params['contributionPageID'];
         *  $params['contributionType_accounting_code'];
         *  $params['amount_level'];
         *  $params['description'];
         ************************************************************************************/
        return $requestFields;
    }
	

    /**********************************************************
     * This function sends request and receives response from
     * the processor
     **********************************************************/
    function doDirectPayment( &$params )
    {
        if ( $params['is_recur'] == true ) {
            CRM_Core_Error::fatal( ts( 'Barclay - recurring payments not implemented' ) );
        }

        if ( ! defined( 'CURLOPT_SSLCERT' ) ) {
            CRM_Core_Error::fatal( ts( 'Barclay requires curl with SSL support' ) );
        }


        $host	 = $this->_paymentProcessor['url_site'];
        /*
         *Create the array of variables to be sent to the processor from the $params array
         * passed into this function
         *
         *Note the requestFields array is a multidimensional array that reflects th
         *xml eg. <user><name> is $requestFields['User']['Name']
         */
             
        $requestFields = self::mapProcessorFieldstoParams($params);

        /*
         * define variables for connecting with the gateway
         */

        // Allow further manipulation of the arguments via custom hooks ..
        CRM_Utils_Hook::alterPaymentProcessorParams( $this, $params, $requestFields );
        /**********************************************************
         * Check to see if we have a duplicate before we send
         **********************************************************/
        if ( $this->_checkDupe( $params['invoiceID'] ) ) {
            return self::errorExit(9003, 'It appears that this transaction is a duplicate.  Have you already submitted the form once?  If so there may have been a connection problem.  Check your email for a receipt.  If you do not receive a receipt within 2 hours you can try your transaction again.  If you continue to have problems please contact the site administrator.' );
        }
        /**********************************************************
         * Convert to XML using function below
         **********************************************************/
       // $xml = urlencode(self::buildXML($requestFields));
		$xml = self::buildXML($requestFields,826);
	
        /**********************************************************
         * Send to the payment processor using cURL
         **********************************************************/


        $ch = curl_init ($host);
        if ( ! $ch ) {
            return self::errorExit(9004, 'Could not initiate connection to payment gateway');
        }

		if ($this->_paymentProcessor['url_site']){
			curl_setopt($ch, CURLOPT_CAINFO, $this->_paymentProcessor['url_site']); // Set the location of the CA-bundle if required
			//This generally is required for Windows users
		}
        curl_setopt($ch, CURLOPT_POST,           true        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true        );  // return the result on success, FALSE on failure 
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $xml ); 
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);   // return the result on success, FALSE on failure
        curl_setopt ($ch, CURLOPT_TIMEOUT,  36000 );
#To do - remove these - make people put certs in
//		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);   // see - http://curl.haxx.se/docs/sslcerts.html
        curl_setopt ($ch,CURLOPT_VERBOSE,1 );         // set this for debugging -look for output in apache error log
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1 );  // ensures any Location headers are followed

        /**********************************************************
         * Send the data out over the wire
         **********************************************************/
        $responseData = curl_exec($ch);

        /**********************************************************
         * See if we had a curl error - if so tell 'em and bail out
         *
         * NOTE: curl_error does not return a logical value (see its documentation), but
         *       a string, which is empty when there was no error.
         **********************************************************/
        if ( (curl_errno($ch) > 0) || (strlen(curl_error($ch)) > 0) ) {
            curl_close($ch);
            $errorNum  = curl_errno($ch);
            $errorDesc = curl_error($ch);

            if ($errorNum == 0)                                               // Paranoia - in the unlikley event that 'curl' errno fails
                $errorNum = 9005;

            if (strlen($errorDesc) == 0)                                      // Paranoia - in the unlikley event that 'curl' error fails
                $errorDesc = "Connection to payment gateway failed";
            if ($errorNum = 60) {
                return self::errorExit( $errorNum, "Curl error - ".$errorDesc." host- $host Try this link for more information http://curl.haxx.se/docs/sslcerts.html" );
            }

            return self::errorExit( $errorNum, "Curl error - ".$errorDesc." your key is located at ".$key." the url is ".$host." xml is ".$requestxml." processor response = ". $processorResponse );
        }
 		
        /**********************************************************
         * If null data returned - tell 'em and bail out
         *
         * NOTE: You will not necessarily get a string back, if the request failed for
         *       any reason, the return value will be the boolean false.
         **********************************************************/
        if ( ( $responseData === false )  || (strlen($responseData) == 0) ) {
            curl_close( $ch);
            return self::errorExit( 9006, "Error: Connection to payment gateway failed - no data returned.");
        }
 		
        /**********************************************************
         // If gateway returned no data - tell 'em and bail out
         **********************************************************/
        if ( empty($responseData) ) {
            curl_close( $ch);
            return self::errorExit( 9007, "Error: No data returned from payment gateway.");		
        }

        /**********************************************************
         // Success so far - close the curl and check the data
         **********************************************************/
        curl_close( $ch);

        /**********************************************************
         * Payment succesfully sent to gateway - process the response now
         **********************************************************/

        $processorResponse = self::decodeXMLResponse($responseData);
        if ( $processorResponse['EngineDoc']['OrderFormDoc']['Transaction']['CardProcResp']['CcErrCode'] == 1) {

        	/*
             * Success !
             */
            if ( $this->_mode == 'test')  {
         /*success in test mode returns response "APPROVED"
         * test mode always returns trxn_id = 0
         * fix for CRM-2566
         **********************************************************/
            	
                $query   = "SELECT MAX(trxn_id) FROM civicrm_contribution WHERE trxn_id LIKE 'test%'";
                $p       = array( );
                $trxn_id = strval( CRM_Core_Dao::singleValueQuery( $query, $p ) );
                $trxn_id = str_replace( 'test', '', $trxn_id );
                $trxn_id = intval($trxn_id) + 1;
                $params['trxn_id'] = sprintf('test%08d', $trxn_id);
                return $params;
            } else {
                $params['status_id'] = 1;
                $params['trxn_id'] = $processorResponse['EngineDoc']['DocumentId'];//'trxn_id' is varchar(255) field. returned value is length 37
                $params['trxn_result_code'] = $processorResponse['EngineDoc']['OrderFormDoc']['Transaction']['CardProcResponse']['ProcReturnMsg'] ;
				return $params;
            }
           
        }else{
	//echo "<pre>THERE";
    // print_r($processorResponse['EngineDoc']['OrderFormDoc']['Transaction']['CardProcResp']['CcErrCode']);
     //echo "</pre>";
           return self::errorExit( 9009, "<p><em>Error:</em> " .$processorResponse['EngineDoc']['MessageList']['Message']['Text']."</p>");	
        }
    } // end function doDirectPayment

    /**
     * Checks to see if invoice_id already exists in db
     * @param  int     $invoiceId   The ID to check
     * @return bool                  True if ID exists, else false
     */
    function _checkDupe( $invoiceId )
    {
        require_once 'CRM/Contribute/DAO/Contribution.php';
        $contribution =& new CRM_Contribute_DAO_Contribution( );
        $contribution->invoice_id = $invoiceId;
        return $contribution->find( );
    }

    /**************************************************
     * Produces error message and returns from class
     **************************************************/
    function &errorExit ( $errorCode = null, $errorMessage = null )
    {
        $e =& CRM_Core_Error::singleton( );
        if ( $errorCode ) {
            $e->push( $errorCode, 0, null, $errorMessage );
        } else {
            $e->push( 9000, 0, null, 'Unknown System Error.' );
        }
        return $e;
    }


    /**************************************************
     * NOTE: 'doTransferCheckout' not implemented
     **************************************************/
    function doTransferCheckout( &$params, $component )
    {
        CRM_Core_Error::fatal( ts( 'This function is not implemented' ) );
    }


    /********************************************************************************************
     * This public function checks to see if we have the right processor config values set
     *
     * NOTE: Called by Events and Contribute to check config params are set prior to trying
     *  register any credit card details
     *
     * @param string $mode the mode we are operating in (live or test) - not used
     *
     * returns string $errorMsg if any errors found - null if OK
     *
     ********************************************************************************************/
    //  function checkConfig( $mode )          // CiviCRM V1.9 Declaration
    function checkConfig( )                // CiviCRM V2.0 Declaration
    {
        $errorMsg = array();

        if ( empty( $this->_paymentProcessor['user_name'] ) ) {
            $errorMsg[] = ' ' . ts( 'ssl_merchant_id is not set for this payment processor' );
        }

        if ( empty( $this->_paymentProcessor['url_site'] ) ) {
            $errorMsg[] = ' ' . ts( 'URL is not set for this payment processor' );
        }

        if ( ! empty( $errorMsg ) ) {
            return implode( '<p>', $errorMsg );
        } else {
            return null;
        }
    }//end check config

    function buildXML($requestFields,$currency)
    {
      require_once('CRM/Core/Payment/arrayXmlConvert.php');
      
   	  $xml = new SimpleXMLElement('<EngineDocList></EngineDocList>');
	  $xml-> addChild('DocVersion','1.0');
      $EngineDoc = $xml->addChild('EngineDoc');
	  $EngineDoc->addChild('ContentType','OrderFormDoc');
	  // Convert the multidimensional array to XML
	  $requestXML = ArrayToXML::toXml($requestFields, 'EngineDoc', &$EngineDoc);
	  //Add attributes 'Currency' and Datatype to 'amount' and 'expires'	 
	  $xml->EngineDoc->OrderFormDoc->Transaction->CurrentTotals->Totals->Total->addAttribute('Currency',$currency);
	  $xml->EngineDoc->OrderFormDoc->Transaction->CurrentTotals->Totals->Total->addAttribute('DataType','Money');
	  $xml->EngineDoc->OrderFormDoc->Consumer->PaymentMech->CreditCard->Expires->addAttribute('Locale',$currency);
	  $xml->EngineDoc->OrderFormDoc->Consumer->PaymentMech->CreditCard->Expires->addAttribute('DataType','ExpirationDate');
		  
	  return($xml->asXML());
    }
    

    function tidyStringforXML($value,$fieldlength)
    {
        //Not currently implemented for Barclay but in processor this was copied from and may be 
        //needed if there is a possibilty we are passing strings that are too long 
        //the xml is posted to a url so must not contain spaces etc. It also needs to be cut off at a certain
        // length to match the processor's field length. The cut needs to be made after spaces etc are
        // transformed but must not include a partial transformed character e.g. %20 must be in or out not half-way
        $xmlString   = substr(rawurlencode($value),0,$fieldlength);
        $lastPercent = 	strrpos($xmlString,'%');
        if ($lastPercent >  $fieldlength- 3) {
            $xmlString = substr($xmlString,0,$lastPercent);
        }
        return $xmlString;
    }

 
    function decodeXMLresponse($responseData)
    {
     
        $xml = simplexml_load_string($responseData) or die ("Unable to load XML string!");
        
        $processorResponse = ArrayToXML::toArray($responseData);
       // print_r($processorResponse);


        return  $processorResponse;
	}
}


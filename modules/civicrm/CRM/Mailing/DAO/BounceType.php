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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_Mailing_DAO_BounceType extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_mailing_bounce_type';
    /**
     * static instance to hold the field values
     *
     * @var array
     * @static
     */
    static $_fields = null;
    /**
     * static instance to hold the FK relationships
     *
     * @var string
     * @static
     */
    static $_links = null;
    /**
     * static instance to hold the values that can
     * be imported / apu
     *
     * @var array
     * @static
     */
    static $_import = null;
    /**
     * static instance to hold the values that can
     * be exported / apu
     *
     * @var array
     * @static
     */
    static $_export = null;
    /**
     * static value to see if we should log any modifications to
     * this table in the civicrm_log table
     *
     * @var boolean
     * @static
     */
    static $_log = false;
    /**
     *
     * @var int unsigned
     */
    public $id;
    /**
     * Type of bounce
     *
     * @var enum('AOL', 'Away', 'DNS', 'Host', 'Inactive', 'Invalid', 'Loop', 'Quota', 'Relay', 'Spam', 'Syntax', 'Unknown')
     */
    public $name;
    /**
     * A description of this bounce type
     *
     * @var string
     */
    public $description;
    /**
     * Number of bounces of this type required before the email address is put on bounce hold
     *
     * @var int unsigned
     */
    public $hold_threshold;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_mailing_bounce_type
     */
    function __construct()
    {
        parent::__construct();
    }
    /**
     * returns all the column names of this table
     *
     * @access public
     * @return array
     */
    function &fields()
    {
        if (!(self::$_fields)) {
            self::$_fields = array(
                'id' => array(
                    'name' => 'id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                ) ,
                'name' => array(
                    'name' => 'name',
                    'type' => CRM_Utils_Type::T_ENUM,
                    'title' => ts('Name') ,
                    'required' => true,
                    'enumValues' => 'AOL, Away, DNS, Host, Inactive, Invalid, Loop, Quota, Relay, Spam, Syntax, Unknown',
                ) ,
                'description' => array(
                    'name' => 'description',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Description') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'hold_threshold' => array(
                    'name' => 'hold_threshold',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Hold Threshold') ,
                    'required' => true,
                ) ,
            );
        }
        return self::$_fields;
    }
    /**
     * returns the names of this table
     *
     * @access public
     * @return string
     */
    function getTableName()
    {
        return self::$_tableName;
    }
    /**
     * returns if this table needs to be logged
     *
     * @access public
     * @return boolean
     */
    function getLog()
    {
        return self::$_log;
    }
    /**
     * returns the list of fields that can be imported
     *
     * @access public
     * return array
     */
    function &import($prefix = false)
    {
        if (!(self::$_import)) {
            self::$_import = array();
            $fields = & self::fields();
            foreach($fields as $name => $field) {
                if (CRM_Utils_Array::value('import', $field)) {
                    if ($prefix) {
                        self::$_import['mailing_bounce_type'] = & $fields[$name];
                    } else {
                        self::$_import[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_import;
    }
    /**
     * returns the list of fields that can be exported
     *
     * @access public
     * return array
     */
    function &export($prefix = false)
    {
        if (!(self::$_export)) {
            self::$_export = array();
            $fields = & self::fields();
            foreach($fields as $name => $field) {
                if (CRM_Utils_Array::value('export', $field)) {
                    if ($prefix) {
                        self::$_export['mailing_bounce_type'] = & $fields[$name];
                    } else {
                        self::$_export[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
    /**
     * returns an array containing the enum fields of the civicrm_mailing_bounce_type table
     *
     * @return array (reference)  the array of enum fields
     */
    static function &getEnums()
    {
        static $enums = array(
            'name',
        );
        return $enums;
    }
    /**
     * returns a ts()-translated enum value for display purposes
     *
     * @param string $field  the enum field in question
     * @param string $value  the enum value up for translation
     *
     * @return string  the display value of the enum
     */
    static function tsEnum($field, $value)
    {
        static $translations = null;
        if (!$translations) {
            $translations = array(
                'name' => array(
                    'AOL' => ts('AOL') ,
                    'Away' => ts('Away') ,
                    'DNS' => ts('DNS') ,
                    'Host' => ts('Host') ,
                    'Inactive' => ts('Inactive') ,
                    'Invalid' => ts('Invalid') ,
                    'Loop' => ts('Loop') ,
                    'Quota' => ts('Quota') ,
                    'Relay' => ts('Relay') ,
                    'Spam' => ts('Spam') ,
                    'Syntax' => ts('Syntax') ,
                    'Unknown' => ts('Unknown') ,
                ) ,
            );
        }
        return $translations[$field][$value];
    }
    /**
     * adds $value['foo_display'] for each $value['foo'] enum from civicrm_mailing_bounce_type
     *
     * @param array $values (reference)  the array up for enhancing
     * @return void
     */
    static function addDisplayEnums(&$values)
    {
        $enumFields = & CRM_Mailing_DAO_BounceType::getEnums();
        foreach($enumFields as $enum) {
            if (isset($values[$enum])) {
                $values[$enum . '_display'] = CRM_Mailing_DAO_BounceType::tsEnum($enum, $values[$enum]);
            }
        }
    }
}

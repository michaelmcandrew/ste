<?php
require_once 'api/v2/utils.php';
require_once ('CRM/nusoap/nusoap.php');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContactsForOutlook
 *
 * @author mthakral
 */
class ContactsForOutlook {
//put your code here
    private $limitClause='  Limit %1,%2';

    public function GetCount($timestamp) {

        $contactsCountQuery ='Select count(1) NumberOfRows from civicrm_contact';

        $contactsCountWithTimestampQuery = 'Select count(distinct `entity_id`) NumberOfRows
                                                from civicrm_log
                                                where entity_table =\'civicrm_contact\'
                                                and `modified_date`>=%1  ';

        $result;
        if($timestamp == 0) {
            $result= $this->runQuery( $contactsCountQuery,array());
        }
        else {
            $param= array( 1 => array(gmdate("Y-m-d H:i:s",$timestamp), 'String' ) );
            $result= $this->runQuery( $contactsCountWithTimestampQuery,$param);
        }
        if($result->fetch()) {
            return $result->NumberOfRows;
        }
        $result->free();

    }

    public function GetContacts($params,$timestamp) {

        $getContactsQuery='SELECT
                            civicrm_contact.id contact_id
                            ,civicrm_contact.first_name
                            ,civicrm_contact.last_name
                            ,civicrm_contact.middle_name
                            ,civicrm_contact.job_title
                            ,civicrm_contact.suffix_id
                            ,civicrm_contact.nick_name
                            ,civicrm_email.email
                            ,civicrm_contact.organization_name current_employer
                            ,civicrm_contact.gender_id
                            ,civicrm_address.city
                            ,civicrm_address.postal_code
                            ,civicrm_address.state_province_id
                            ,civicrm_phone.phone
                            ,civicrm_address.street_address
                            ,civicrm_address.supplemental_address_1
                            ,civicrm_address.supplemental_address_2
                            FROM civicrm_contact
                            LEFT OUTER JOIN civicrm_address ON civicrm_contact.id = civicrm_address.contact_id AND civicrm_address.is_primary =1
                            LEFT OUTER JOIN civicrm_email ON civicrm_contact.id = civicrm_email.contact_id AND civicrm_email.is_primary =1
                            LEFT OUTER  JOIN civicrm_phone ON civicrm_contact.id = civicrm_phone.contact_id AND civicrm_phone.is_primary =1  ';

        $appendWithtimeStamp=' Where  civicrm_contact.id in (
                                   Select distinct `entity_id`
                                                from civicrm_log
                                                where entity_table =\'civicrm_contact\'
                                                and `modified_date`>=%3  )  ';



        $dbParam= array();
        $dbParam[1] = array($params['offset'], 'Integer' ) ;
        $dbParam[2] = array($params['rowCount'], 'Integer' ) ;

        $query = $getContactsQuery;
        if($timestamp != 0) {
            $dbParam[3]= array(gmdate("Y-m-d H:i:s",$timestamp), 'String' ) ;
            $query .= $appendWithtimeStamp;
        }
        $query.=$this->limitClause;

        //nusoap_base::debug($query);

        $contactList = array();
        $i=0;

        $result= $this->runQuery($query,$dbParam);

        while($result->fetch()) {
            $contact = array();

            foreach((array)$result as $key => $value) {
                if($key!='N') {
                    if(substr($key,0,1)!='_') {
                        $contact[$key]=$value;
                    }
                }
            }

            $contactList[$i]=$contact;
            $i++;
        //return $result->result;
        }
        $result->free();

        // CRM_Core_Error::debug('Let us try this to verify',$contactList );
        return $contactList;

    }

    public function GetDistList($params) {

        $getDistListQuery ='SELECT
                                    \'Group\' type
                                    ,civicrm_group.id
                                    ,civicrm_group.title,
                                    GROUP_CONCAT( CONCAT( CONCAT_WS( \' \', civicrm_contact.first_name, civicrm_contact.middle_name, civicrm_contact.last_name ) , \' (\', IFNULL( civicrm_email.email, \'\' ) , \')\' ) SEPARATOR \', \' ) recipients
                                FROM civicrm_group
                                INNER JOIN civicrm_group_contact ON civicrm_group.id = civicrm_group_contact.group_id
                                INNER JOIN civicrm_contact ON civicrm_contact.id = civicrm_group_contact.contact_id
                                INNER JOIN civicrm_email ON civicrm_contact.id = civicrm_email.contact_id
                                WHERE civicrm_email.is_primary =1
                                AND civicrm_group.is_active=1
                                AND civicrm_group_contact.status=\'Added\'
                                GROUP BY civicrm_group.title
                                Union
                                -- Tag Query
                                SELECT
                                        \'Tag\'
                                        ,civicrm_tag.id type
                                        ,civicrm_tag.Name,
                                        GROUP_CONCAT( CONCAT( CONCAT_WS( \' \', civicrm_contact.first_name, civicrm_contact.middle_name, civicrm_contact.last_name ) , \' (\', IFNULL( civicrm_email.email, \'\' ) , \')\' ) SEPARATOR \', \' ) recipients
                                FROM civicrm_tag
                                INNER JOIN civicrm_entity_tag ON civicrm_tag.id = civicrm_entity_tag.tag_id
                                INNER JOIN civicrm_contact ON civicrm_contact.id = civicrm_entity_tag.contact_id
                                INNER JOIN civicrm_email ON civicrm_contact.id = civicrm_email.contact_id
                                WHERE civicrm_email.is_primary =1
                                GROUP BY civicrm_tag.Name  ';

        $dbParam= array();
        $dbParam[1] = array($params['offset'], 'Integer' ) ;
        $dbParam[2] = array($params['rowCount'], 'Integer' ) ;

        $query = $getDistListQuery;
        $query .= $this->limitClause;
        $result=  $this->runQuery($query,$dbParam);

        $DistListsList = array();
        $i=0;

        while($result->fetch()) {
            $DistList = array();
            foreach((array)$result as $key => $value) {
                if($key!='N') {
                    if(substr($key,0,1)!='_') {
                        $DistList[$key]=$value;
                    }
                }
            }
            $DistListsList[$i]=$DistList;
            $i++;
        }
        //CRM_Core_Error::debug('Let us try this to verify',$DistListsList );
        $result->free();
        return $DistListsList;

    }

    public function GetDistListCount() {
        $query='Select (Count(Distinct tag_id)+table2.count) TotalDistList
                from civicrm_entity_tag
                ,(Select count(Distinct group_id) count
                from civicrm_group_contact
                inner join civicrm_group
                on civicrm_group.id=civicrm_group_contact.group_id
                where  civicrm_group.is_active=1
                and civicrm_group_contact.status=\'Added\'
                ) table2';

        $result= $this->runQuery( $query,array());

        if($result->fetch()) {
            $count=$result->TotalDistList;
            $result->free();
            return  $count;
        }

    }
    private function runQuery($query,$params) {
        _civicrm_initialize();

        $queryResult = CRM_Core_DAO :: executeQuery($query,$params);


        return $queryResult;
    }

}
?>

<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'Mail/mime.php';
require_once 'CRM/Utils/Mail.php';

class CRM_Mailing_Event_BAO_Resubscribe {

    /**
     * Resubscribe a contact to the groups, he/she was unsubscribed from.
     *
     * @param int $job_id       The job ID
     * @param int $queue_id     The Queue Event ID of the recipient
     * @param string $hash      The hash
     * @return array|null $groups    Array of all groups to which the contact was added, or null if the queue event could not be found.
     * @access public
     * @static
     */
    public static function &resub_to_mailing($job_id, $queue_id, $hash) {
        /* First make sure there's a matching queue event */
        $q =& CRM_Mailing_Event_BAO_Queue::verify($job_id, $queue_id, $hash);
        if (! $q) {
            return null;
        }

        // check if this queue_id was actually unsubscribed 
        $ue =& new CRM_Mailing_Event_BAO_Unsubscribe();
        $ue->event_queue_id = $queue_id;
        $ue->org_unsubscribe = 0;
        if (! $ue->find(true)) {
            return null;
        }
        
        $contact_id = $q->contact_id;
        
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $do =& new CRM_Core_DAO();
        $mg         = CRM_Mailing_DAO_Group::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();
        $mailing    = CRM_Mailing_BAO_Mailing::getTableName();
        $group      = CRM_Contact_BAO_Group::getTableName();
        $gc         = CRM_Contact_BAO_GroupContact::getTableName();
        
        $do->query("
            SELECT      $mg.entity_table as entity_table,
                        $mg.entity_id as entity_id
            FROM        $mg
            INNER JOIN  $job
                ON      $job.mailing_id = $mg.mailing_id
            WHERE       $job.id = " 
                . CRM_Utils_Type::escape($job_id, 'Integer') . "
                AND     $mg.group_type = 'Include'");
        
        /* Make a list of groups and a list of prior mailings that received 
         * this mailing */
         
        $groups = array();
        $mailings = array();
        
        while ($do->fetch()) {
            if ($do->entity_table == $group) {
                $groups[$do->entity_id] = null;
            } else if ($do->entity_table == $mailing) {
                $mailings[] = $do->entity_id;
            }
        }
        
        /* As long as we have prior mailings, find their groups and add to the
         * list */
        while (! empty($mailings)) {
            $do->query("
                SELECT      $mg.entity_table as entity_table,
                            $mg.entity_id as entity_id
                FROM        $mg
                WHERE       $mg.mailing_id IN (".implode(', ', $mailings).")
                    AND     $mg.group_type = 'Include'");
            
            $mailings = array();
            
            while ($do->fetch()) {
                if ($do->entity_table == $group) {
                    $groups[$do->entity_id] = true;
                } else if ($do->entity_table == $mailing) {
                    $mailings[] = $do->entity_id;
                }
            }
        }

        /* Now we have a complete list of recipient groups.  Filter out all
         * those except smart groups and those that the contact belongs to */
        $do->query("
            SELECT      $group.id as group_id,
                        $group.title as title
            FROM        $group
            LEFT JOIN   $gc
                ON      $gc.group_id = $group.id
            WHERE       $group.id IN (".implode(', ', array_keys($groups)).")
                AND     ($group.saved_search_id is not null
                            OR  ($gc.contact_id = $contact_id
                                AND $gc.status = 'Removed')
                        )");
                        
        while ($do->fetch()) {
            $groups[$do->group_id] = $do->title;
        }

        $contacts = array($contact_id);
        foreach ($groups as $group_id => $group_name) {
            if ($group_name) {
                list($total, $added, $notadded) = CRM_Contact_BAO_GroupContact::addContactsToGroup( $contacts, $group_id, 'Email');
            }
            if ($notadded) {
                unset($groups[$group_id]);
            }
        }
        
        // remove entry from Unsubscribe table.
        $ue =& new CRM_Mailing_Event_BAO_Unsubscribe();
        $ue->event_queue_id = $queue_id;
        $ue->org_resubscribe = 0;
        if ($ue->find(true)) {
            $ue->delete();
        }

        $transaction->commit( );
        return $groups;
    }


    /**
     * Send a reponse email informing the contact of the groups to which he/she
     * has been resubscribed.
     *
     * @param string $queue_id      The queue event ID
     * @param array $groups         List of group IDs
     * @param bool $is_domain       Is this domain-level?
     * @param int $job              The job ID
     * @return void
     * @access public
     * @static
     */
    public static function send_resub_response($queue_id, $groups, $is_domain = false, $job) {
        // param is_domain is not supported as of now.

        $config =& CRM_Core_Config::singleton();
        $domain =& CRM_Core_BAO_Domain::getDomain( );

        $jobTable = CRM_Mailing_BAO_Job::getTableName();
        $mailingTable = CRM_Mailing_DAO_Mailing::getTableName();
        $contacts = CRM_Contact_DAO_Contact::getTableName();
        $email    = CRM_Core_DAO_Email::getTableName();
        $queue    = CRM_Mailing_Event_BAO_Queue::getTableName();
      
        //get the default domain email address.
        list( $domainEmailName, $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );
        
        $dao =& new CRM_Mailing_BAO_Mailing();
        $dao->query("   SELECT * FROM $mailingTable 
                        INNER JOIN $jobTable ON
                            $jobTable.mailing_id = $mailingTable.id 
                        WHERE $jobTable.id = $job");
        $dao->fetch();

        $component =& new CRM_Mailing_BAO_Component();
        $component->id = $dao->resubscribe_id;
        $component->find(true);

        $html = $component->body_html;
        if ($component->body_text) {
            $text = $component->body_text;
        } else {
            $text = CRM_Utils_String::htmlToText($component->body_html);
        }

        $eq =& new CRM_Core_DAO();
        $eq->query(
        "SELECT     $contacts.preferred_mail_format as format,
                    $contacts.id as contact_id,
                    $email.email as email,
                    $queue.hash as hash
        FROM        $contacts
        INNER JOIN  $queue ON $queue.contact_id = $contacts.id
        INNER JOIN  $email ON $queue.email_id = $email.id
        WHERE       $queue.id = " 
                    . CRM_Utils_Type::escape($queue_id, 'Integer'));
        $eq->fetch();
        foreach ( $groups as $key => $value ) {
            if (!$value) {
                unset($groups[$key]);
            }
        }

        // we need to wrap Mail_mime because PEAR is apparently unable to fix
        // a six-year-old bug (PEAR bug #30) in Mail_mime::_encodeHeaders()
        // this fixes CRM-5466
        require_once 'CRM/Utils/Mail/FixedMailMIME.php';
        $message =& new CRM_Utils_Mail_FixedMailMIME("\n");

        list($addresses, $urls) = CRM_Mailing_BAO_Mailing::getVerpAndUrls($job, $queue_id, $eq->hash, $eq->email);
        $bao =& new CRM_Mailing_BAO_Mailing();
        $bao->body_text = $text;
        $bao->body_html = $html;
        $tokens = $bao->getTokens();
        require_once 'CRM/Utils/Token.php';
        if ($eq->format == 'HTML' || $eq->format == 'Both') {
            $html = 
                CRM_Utils_Token::replaceDomainTokens($html, $domain, true, $tokens['html']);
            $html = 
                CRM_Utils_Token::replaceResubscribeTokens($html, $domain, $groups, true, $eq->contact_id, $eq->hash);
            $html = CRM_Utils_Token::replaceActionTokens($html, $addresses, $urls, true, $tokens['html']);
            $html = CRM_Utils_Token::replaceMailingTokens($html, $dao, null, $tokens['html']);
            $message->setHTMLBody($html);
        }
        if (!$html || $eq->format == 'Text' || $eq->format == 'Both') {
            $text = 
                CRM_Utils_Token::replaceDomainTokens($html, $domain, true, $tokens['text']);
            $text = 
                CRM_Utils_Token::replaceResubscribeTokens($text, $domain, $groups, false, $eq->contact_id, $eq->hash);
            $text = CRM_Utils_Token::replaceActionTokens($text, $addresses, $urls, false, $tokens['text']);
            $text = CRM_Utils_Token::replaceMailingTokens($text, $dao, null, $tokens['text']);
            $message->setTxtBody($text);
        }

        require_once 'CRM/Core/BAO/MailSettings.php';
        $emailDomain = CRM_Core_BAO_MailSettings::defaultDomain();

        $headers = array(
                         'Subject'       => $component->subject,
                         'From'          => "\"$domainEmailName\" <do-not-reply@$emailDomain>",
                         'To'            => $eq->email,
                         'Reply-To'      => "do-not-reply@$emailDomain",
                         'Return-Path'   => "do-not-reply@$emailDomain",
                         );
        
        $b =& CRM_Utils_Mail::setMimeParams( $message );
        $h =& $message->headers($headers);

        $mailer =& $config->getMailer();
        
        PEAR::setErrorHandling( PEAR_ERROR_CALLBACK,
                                array('CRM_Core_Error', 'nullHandler' ) );
        if ( is_object( $mailer ) ) {
            $mailer->send($eq->email, $h, $b);
            CRM_Core_Error::setCallback();
        }
    }

}


<?php
namespace HTTA\Control\Email;

use HTTA\Database\Database;
use HTTA\Database\EmailMessageTable;
use HTTA\Lib\Utils;
use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;


class Emailer {

  const SEND_RES_OK = 1;
  const SEND_RES_SEND_FAILED = -1;
  const SEND_RES_MESSAGE_MISSING = -2;
  const SEND_RES_RECIPIENT_EMAIL_MISSING = -3;

  const STATUS_SENT = 'S';
  const STATUS_ERROR = 'E';

  const MSG_REF_VISITOR = 'visitor';
  const MSG_REF_ADMIN = 'admin';

  private ?PHPMailer $mailer;
  private \HTTA\Database\EmailMessageTable $emailMessageTable;
  private array $emailList;
  private bool $success = false;

  const EMAIL_KEY_RESENT = 'r';
  const EMAIL_KEY_PENDING = 'p';
  const EMAIL_KEY_LIST = 'l';
  const EMAIL_KEY_COUNT = 'c';
  const EMAIL_KEY_MSG = 'm';

  public function __construct() {
    date_default_timezone_set("Europe/London"); // ensure time zone
    $this->emailMessageTable = new EmailMessageTable();
    $this->setupPhpEmailer();
  }

  /**
   * find all pending emails and all failed emails for a second attempt and sends them
   */
  private function fetchEmailQueue(): void {
    $this->emailList = $this->fetchEmailList();
  }

  /**
   * find all pending emails and all failed emails for a second attempt and sends them
   */
  public function processEmailQueue(): bool {
    $this->fetchEmailQueue();
    $this->sendEmails();
    return $this->success;
  }

  /**
   * Assign the settings for the PHPMailer object, called from GetEmailer
   */
  private function setupPhpEmailer(): void {
    $this->mailer = new PHPMailer();
    $this->mailer->isSMTP(); // Tell PHPMailer to use SMTP
    $this->mailer->CharSet = "UTF-8";
    $this->mailer->SMTPDebug = 0;
    $this->mailer->Host = SETTINGS_HOST;
    $this->mailer->Port = SETTINGS_PORT;
    $this->mailer->SMTPSecure = SETTINGS_SMTP_SECURE; // Set the encryption system to use - ssl (deprecated) or tls
    $this->mailer->SMTPAuth = SETTINGS_SMTP_AUTH; // Whether to use SMTP authentication
    $this->mailer->Username = SETTINGS_SMTP_USERNAME; // Username to use for SMTP authentication - use full email address for gmail
    $this->mailer->Password = SETTINGS_SMTP_PASSWORD; // Password to use for SMTP authentication
//    $this->mail->SMTPOptions = ($this->settings->ssloptions)
//      ? ['ssl' => $this->settings->ssloptions] : [];
    $this->mailer->setFrom(SETTINGS_SENDER_EMAIL, SETTINGS_SENDER_NAME);
    $this->mailer->addReplyTo(SETTINGS_REPLY_EMAIL, SETTINGS_REPLY_NAME);
  }

  /**
   * Send a list of emails (list of email id)
   * @param $list
   * @return void number of emails sent successfully
   */
  private function sendEmails(): void {
//    $sent = 0;
//    $count = count($list);
    // therefore SendEmail always returns true for now
    foreach($this->emailList as $emailId) {
      $this->sendEmail($emailId);
//      if ($this->sendEmail($email)) {
//        $sent++;
//      }
    }
  }

  private function getEmailMessageByRef($msgRef): string {
    $msg = match ($msgRef) {
      self::MSG_REF_ADMIN => $this->getAdminMessage(),
      self::MSG_REF_VISITOR => $this->getVisitorMessage(),
      default => [],
    };
    return Utils::arrayToString($msg, PHP_EOL);
  }

  private function getAdminMessage(): array {
    return [
      '<p>You have received a new message from the HTTA Website.</p>',
      '<br>',
      '<p><b>Name:</b> ' . $this->emailMessageTable->getFieldValue('name') . '</p>',
      '<p><b>Email:</b> ' . $this->emailMessageTable->getFieldValue('emailaddress') . '</p>',
      '<p><b>Message:</b></p>',
      '<blockquote> ' . nl2br(html_entity_decode(
        $this->emailMessageTable->getFieldValue('message')
      )). '</blockquote>',
      '<br>',
      '*** End of Message ***'
    ];
  }

  private function getVisitorMessage(): array {
    return [
      '<p>Thank you for your message you made from our website.</p>',
      '<p>We will be in touch soon with a reply.</p>',
      '<br>',
      '<p>Kind regards</p>',
      '<p>HTTA Web Manager</p>',
      '<br>'
    ];
  }

//  private function assignCustomData() {
//    $list = [];
//    $cd = $this->currentEmail->getFieldValue('customdata', '', false, false);
//    $customdata = explode("\n", str_replace("\r", '', $cd));
//    if ($customdata) {
//      foreach($customdata as $cfitem) {
//        if ($cfitem) {
//          $pos = strpos($cfitem, '=');
//          if ($pos) {
//            $key = substr($cfitem, 0, $pos);
//            $val = substr($cfitem, $pos+1);
//            if ($key) {
//              $list[$key] = nl2br(\html_entity_decode($val));
//            }
//          }
//        }
//      }
//    }
//    $this->customFields = $list;
//  }

  private function processSent(): bool {
    $this->success = true;
    $this->emailMessageTable->setFieldValue(\HTTA\Database\TableBase::FN_STATUS, self::STATUS_SENT);
    $this->emailMessageTable->setFieldValue('sentstamp', date(Utils::DATE_STD_WITH_TIME));
    return $this->emailMessageTable->storeChanges() !== \HTTA\Database\TableBase::STORE_RESULT_ERROR;
  }

  private function processFail(): void {
    $this->success = false;
    $this->emailMessageTable->setFieldValue(\HTTA\Database\TableBase::FN_STATUS, self::STATUS_ERROR);
    $this->emailMessageTable->storeChanges();
    $this->emailMessageTable->storeChanges() !== \HTTA\Database\TableBase::STORE_RESULT_ERROR;
  }

  private function doSend(string $subject, string $msgRef, string $email, string $name): int {
    $ret = self::SEND_RES_OK;
    //$emailmessage = $this->emailmessageProcessor->getTable($this->currentmessageid);
    $this->mailer->clearAddresses();
    $this->mailer->addAddress($email, $name);
//      $this->assignCustomData();
    $message = $this->getEmailMessageByRef($msgRef);
    $this->mailer->Subject = $subject; // CONTACT_SUBJECT;
    $this->mailer->msgHTML($message); //nl2br(stripslashes($message)));
    $this->mailer->AltBody = '';
    if (!$this->mailer->send()) {
      $ret = self::SEND_RES_SEND_FAILED;
    }
    return $ret;
  }

  /**
   * SendEmail - sends a message based on the row in the email table
   * @param int $emailId
   * @return boolean always true - ignore for now
   */
  private function sendEmail(int $emailId): bool {
    $ret = false;
    $this->emailMessageTable->findByKey($emailId);
    if ($this->emailMessageTable->exists) { // email message exists
      $sendRes = $this->doSend(
        CONTACT_SUBJECT, self::MSG_REF_VISITOR,
        $this->emailMessageTable->getFieldValue('emailaddress'),
        $this->emailMessageTable->getFieldValue('name')
      );
      if ($sendRes === self::SEND_RES_OK) {
        $sendRes = $this->doSend(
          CONTACT_SUBJECT, self::MSG_REF_ADMIN,
          SETTINGS_SENDER_EMAIL, SETTINGS_SENDER_NAME
        );
        $ret = $this->processSent();
      }
    }
    if (!$ret) {
      $this->processFail();
    }
    return $ret;
  }

  private function fetchEmailList(): array {
    $query = "SELECT `id` FROM `emailmessage` WHERE `status` = 'P'";
    $list = [];
    $result = Database::query($query);
    while ($line = $result->fetch_assoc()) {
      $id = $line['id'];
      $list[$id] = $id;
    }
    $result->close();
    return $list;
  }

}

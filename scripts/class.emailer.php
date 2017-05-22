<?php

/*
 * email wrapper
 */

namespace dana\email;

/**
 * Description of class
 *
 * @author white
 */
class emailer {
  protected $mail;

  public $host = "n1plcpnl0081.prod.ams1.secureserver.net";
  public $port = 465;
  public $SMTPAuth = true;
  public $username = 'support@haverhill-twintown.org';
  public $password = '13FirnGrove13';

  function __construct() {
    date_default_timezone_set('Etc/UTC');
    require 'PHPMailer\class.phpmailer.php';
    $this->mail = new PHPMailer();
    $this->Setup();
  }

  protected function Setup() {
    $this->mail->isSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
    $this->mail->SMTPDebug = 2;
//Ask for HTML-friendly debug output
    $this->mail->Debugoutput = 'html';

    $this->mail->Host = "mail.example.com";
    $this->mail->Port = 25;
    $this->mail->SMTPAuth = true;
    $this->mail->Username = "yourname@example.com";
    $this->mail->Password = "yourpassword";
  }

  public function SetFrom($addr, $name = false) {
    $this->mail->setFrom($addr, $name);
  }

  public function SetReplyTo($addr, $name = false) {
    $this->mail->addReplyTo($addr, $name);
  }

  public function SetRecipient($addr, $name = false) {
    $this->mail->addAddress($addr, $name);
  }

  public function SetSubject($value) {
    $this->mail->Subject = $value;
  }

  public function AssignContent($text, $html = false) {
    if ($html) {
      $this->mail->msgHTML($html);
      $this->mailAltBody = $text;
    } else {
      $this->mail->Body = $text;
    }
  }

  public function AddAttachment($filename) {
    $this->mailaddAttachment($filename);
  }

  public function Send() {
    return $this->mail->send();
//  echo "Mailer Error: " . $this->mail->ErrorInfo;
  }
}

<?php
namespace dana\display;

require_once 'define.php';
require_once 'class.database.php';
require_once 'class.table.eventinterest.php';
require_once 'PHPMailer/class.phpmailer.php';

class sendinterestedemail {
  private $eventid;
  private $numberinterested;
  private $email;

  function __construct() {
    $this->eventid = GetGet('event');
    $this->numberinterested = GetGet('num');
    $this->email = GetGet('email');
    $this->StoreInterest();
    $this->SendEmailToVisitor();
    $this->SendEmailToAdmin();
    $this->ShowMessage();
  }

  private function ShowMessage() {
    echo "<p class='thanks'>Thank you for your interest, we'll keep you updated.</p>";
  }

  private function SendEmailToVisitor() {
    $this->SendEmail($this->email);
  }

  private function SendEmailToAdmin() {
    ;
  }

  private function SendEmail($email) {
    ;
  }

  private function StoreInterest() {
    $eventinterest = new \dana\table\eventinterest();
    $eventinterest->MakeRow($this->eventid, $this->email, $this->numberinterested);
  }
}

$sendinterestedemail = new sendinterestedemail();

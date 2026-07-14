<?php
namespace HTTA\Control;

require_once '../Database/Database.php';
require_once '../Lib/Utils.php';
require_once '../Control/Email/Emailer.php';
require_once '../Database/EmailMessageTable.php';

//require_once $cd3; // . '/../../Database/EmailMessageTable.php';
require_once './Email/EmailSettings.php';
//require './PHPMailer/src/Exception.php';
require_once './Email/PHPMailer/src/PHPMailer.php';
require_once './Email/PHPMailer/src/SMTP.php';

use HTTA\Control\Email\Emailer;
use HTTA\Database\Database;
//use HTTA\Database\DatabaseException;
use HTTA\Lib\Utils;

class ProcessMessage {
  private string $status = 'ok';
  private string $message = '';

  function __construct() {
    $data = $_POST;
    if ($data) {
//      $data = $_POST; // Utils::getPost();
      $this->storeMessage($data);
      $this->emailMessage($data);
    } else {
      $this->reportError('No message found');
    }
  }

  private function reportError(string $msg): void {
    $this->status = 'error';
    $this->message = $msg;
  }

  private function reportSuccess(string $msg): void {
    $this->status = 'ok';
    $this->message = $msg;
  }

  private function storeMessage($data): void {
    $message = new \HTTA\Database\EmailMessageTable();
    $message->setFieldValue('name', htmlspecialchars($data['contact-name']));
    $message->setFieldValue('emailaddress', $data['contact-email']);
    $message->setFieldValue('message', htmlspecialchars($data['contact-message']));
    $message->setFieldValue('status', 'P');
    $message->storeChanges();
  }

  private function emailMessage($data): void {
    $emailer = new Emailer();
    if ($emailer->processEmailQueue()) {
      $this->reportSuccess('Your messsage was sent. Thank you.');
    } else {
      $this->reportError('Sorry, there is a problem and your message could not be sent. Please try again later.');
    }
  }

  public function getStatus(): string {
    return $this->status;
  }

  public function getMessage(): string {
    return $this->message;
  }

  static function getDetails(): array {
    $message = new ProcessMessage();
    return [
      'data' => [
        'status' => $message->getStatus(),
        'message' => $message->getMessage()
      ]
    ];
  }
}

header("Content-type:application/json");
echo json_encode(ProcessMessage::getDetails());

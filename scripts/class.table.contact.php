<?php
namespace dana\table;

require_once 'class.basetable.php';

/**
  * contact table
  * @version dana framework v.3
*/

class contact extends idtable {

  function __construct($id = 0) {
    parent::__construct('contact', $id);
  }

  protected function AssignFields() {
    parent::AssignFields();
    $this->AddField('displayname', self::DT_STRING);
    $this->AddField('email', self::DT_STRING);
    $this->AddField('subject', self::DT_STRING);
    $this->AddField('message', self::DT_TEXT);
    $this->AddField('stamp', self::DT_DATETIME);
    $this->AddField('ipaddr', self::DT_STRING);
    $this->AddField('agent', self::DT_STRING);
  }

  protected function AfterPopulateFields() {
  }

  protected function DoPreSave() {
    $ipaddr = $_SERVER['REMOTE_ADDR'];
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
      $ipaddr = str_replace(';', '-', array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
    }
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $this->SetFieldValue('ipaddr', $ipaddr);
    $this->SetFieldValue('agent', $agent);
  }

  private function GetDateTime($date, $time, $gap = ' at ') {
    $ret = '';
    if ($date) {
      $ret = date('D, jS F Y', strtotime($date));
      if ($time) {
        $ret .= $gap . $time;
      }
    }
    return $ret;
  }
}

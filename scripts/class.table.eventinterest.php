<?php
namespace dana\table;

require_once 'class.basetable.php';
require_once 'class.table.media.php';

/**
  * event interest table
  * @version dana framework v.3
*/

class eventinterest extends idtable {

  public $media;

  function __construct($id = 0) {
    parent::__construct('eventinterest', $id);
  }

  protected function AssignFields() {
    parent::AssignFields();
    $this->AddField('eventid', self::DT_FK);
    $this->AddField('email', self::DT_STRING);
    $this->AddField('number', self::DT_INTEGER);
    $this->AddField('createdstamp', self::DT_DATETIME);
  }

  protected function AfterPopulateFields() {
  }

  public function MakeRow($eventid, $email, $number) {
    $this->NewRow();
    $this->SetFieldValue('eventid', $eventid);
    $this->SetFieldValue('email', $email);
    $this->SetFieldValue('number', $number);
    $this->StoreChanges();
    // send email to sender
    // send email to us
  }

  static public function GetInterestedByEventID($eventid) {
    $query = "SELECT `email`, `number` FROM `eventinterest` WHERE `eventid` = {$eventid} ORDER BY `createdstamp`";
    $result = \dana\core\database::Query($query);
    $list = [];
    while ($line = $result->fetch_assoc()) {
      $id = $line['id'];
      $list[$id] = new \dana\table\eventinterest($id);
    }
    $result->close();
    return $list;
  }

  public function Show($includetoc = false) {
  }
}

\dana\table\article::$grouplist = \dana\table\article::GetArticleGroup();

<?php
namespace dana\table;

//use dana\core;

require_once 'class.basetable.php';

/**
  * article group table
  * @version dana framework v.3
*/

class articlegroup extends idtable {

  function __construct($id = 0) {
    parent::__construct('articlegroup', $id);
  }

  protected function AssignFields() {
    parent::AssignFields();
    $this->AddField(self::DT_REF, self::DT_REF);
    $this->AddField('title', self::DT_STRING);
    $this->AddField('info', self::DT_TEXT);
    $this->AddField('special', self::DT_STRING);
    $this->AddField(\dana\table\basetable::FN_STATUS, self::DT_STATUS);
  }

  protected function AfterPopulateFields() {
  }

  public function FormatSimple() {
    $ref = $this->GetFieldValue('ref');
    $title = $this->GetFieldValue('title');
    $description = $this->GetFieldValue('info');
    $ret = [
      "          <section class=\"articlegroup 6u\" style=\"text-align: center; display: block\" data-ref={$ref}>",
      "            <h3><a href=\"#\" title=\"\">{$title}</a></h3>",
      "            <p>{$description}</p>",
      '          </section>'
    ];
    return \ArrayToString($ret);
  }

  static public function GetGroupList($special = false) {
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $specialclause = "AND `special` " . (($special) ? " = '{$special}' " : 'IS NULL ');
    $query =
      'SELECT `id` FROM `articlegroup` ' .
      "WHERE `status` = '{$status}' " . $specialclause .
      'ORDER BY `title`';
    $result = \dana\core\database::Query($query);
    $list = array();
    while ($line = $result->fetch_assoc()) {
      $id = $line['id'];
      $list[$id] = new \dana\table\articlegroup($id);
    }
    $result->close();
    return $list;
  }

  public function GetArticleItems($ref) {
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $query =
      "SELECT a.`id` FROM `article` a " .
      "INNER JOIN `articlegroup` g ON g.`id` = a.`articlegroupid` " .
      "WHERE (g.`ref` = '{$ref}') AND (a.`status` = '{$status}') " .
      "ORDER BY a.`publishdate`";
    $result = \dana\core\database::Query($query);
    $list = [];
    while ($line = $result->fetch_assoc()) {
      $artid = $line['id'];
      $list[$artid] = new \dana\table\article($artid);
    }
    $result->close();
    return $list;
  }
}

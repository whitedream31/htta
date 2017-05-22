<?php
namespace dana\table;

//use dana\core;

require_once 'class.basetable.php';

/**
  * gallery table
  * @version dana framework v.3
*/

class gallery extends idtable {
  public $galleryheight = false;

  function __construct($id = 0) {
    parent::__construct('gallery', $id);
  }

  protected function AssignFields() {
    parent::AssignFields();
    $this->AddField(self::DT_REF, self::DT_REF);
    $this->AddField('title', self::DT_STRING);
    $this->AddField('description', self::DT_STRING);
    $this->AddField(\dana\table\basetable::FN_STATUS, self::DT_STATUS);
  }

  protected function AfterPopulateFields() {
  }

  private function CalcGalleryHeight() {
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $id = $this->ID();
    $query =
      "SELECT MAX(`height`) AS maxht FROM `media` m " .
      "INNER JOIN `galleryitem` gi ON m.`id` = gi.`mediaid` " .
      "WHERE gi.`galleryid` = {$id} AND gi.`status` = '{$status}'";
    $result = \dana\core\database::Query($query);
    $line = $result->fetch_assoc();
    $max = $line['maxht'] + 30;
    $result->close();
    if ($max < 100) {
      $max = 100;
    }
    return $max;
  }

  public function GetGalleryHeight() {
    return $this->CalcGalleryHeight();
  }

  private function GetRandonThumbnail($path) {
    $ret = false;
    if (file_exists($path)) {
      $dir = \scandir($path);
      $files = \array_diff($dir, ['..', '.']);
      $list = [];
      foreach($files as $file) {
        if (\strpos($file, '_sm.')) {
          $list[] = $file;
        }
      }
      if ($list) {
        \shuffle($list);
        $ret = \reset($list);
      }
    }
    return $ret;
  }

  public function FormatSimple() {
    $ref = $this->GetFieldValue('ref');
    $title = $this->GetFieldValue('title');
    $description = $this->GetFieldValue('description');
    $path = 'images/' . $ref;
    $thumbnail = $this->GetRandonThumbnail('../' . $path);
    if ($thumbnail) {
      $img = "<img src=\"{$path}\\{$thumbnail}\" alt=\"\" />";
    }
    $ret = [
      "          <section class=\"gallerygroup 6u\" style=\"text-align: center; display: block\" data-ref={$ref}>",
      "            <h3><a href=\"#\" title=\"\">{$title}</a></h3>",
      "            <a href=\"#\" title=\"\">{$img}</a>",
      "            <p>{$description}</p>",
      '          </section>'
    ];
    return \ArrayToString($ret);
  }

  static public function GetGroupList() {
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $query =
      'SELECT `id` FROM `gallery` ' .
      "WHERE `status` = '{$status}' " .
      'ORDER BY `title`';
    $result = \dana\core\database::Query($query);
    $list = array();
    while ($line = $result->fetch_assoc()) {
      $id = $line['id'];
      $list[$id] = new \dana\table\gallery($id);
    }
    $result->close();
    return $list;
  }

  static public function GetGalleryItems($ref) {
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $query =
      "SELECT gi.`id` FROM `galleryitem` gi " .
      "INNER JOIN `gallery` g ON g.`id` = gi.`galleryid` " .
      "WHERE (g.`ref` = '{$ref}') AND (gi.`status` = '{$status}') " .
      "ORDER BY gi.`title`";
    $result = \dana\core\database::Query($query);
    $list = [];
    while ($line = $result->fetch_assoc()) {
      $galid = $line['id'];
      $list[$galid] = new \dana\table\galleryitem($galid);
    }
    $result->close();
    return $list;
  }
}

<?php
namespace dana\table;

require_once 'class.basetable.php';

/**
  * newsletter table
  * @version dana framework v.3
*/

class newsletter extends idtable {

  static $list = false;
  static $recent = false;

  function __construct($id = 0) {
    parent::__construct('newsletter', $id);
  }

  protected function AssignFields() {
    parent::AssignFields();
    $this->AddField('title', self::DT_STRING);
    $this->AddField('publishdate', self::DT_DATE);
    $this->AddField('url', self::DT_STRING);
    $this->AddField(self::FN_STATUS, self::DT_STATUS);
  }

  protected function AfterPopulateFields() {
  }

  public function DoUpdateDownloadCounter($nl) {
    
  }

  static public function GetNewsletterList($recentduration) {
    $lastdate = date('Y-m-d', strtotime($recentduration));
    self::$list = [];
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $query =
      'SELECT `id`, `publishdate` FROM `newsletter` ' .
      "WHERE `status` = '{$status}' " .
      'ORDER BY `publishdate`, `title`';
    $result = \dana\core\database::Query($query);
    while ($line = $result->fetch_assoc()) {
      $id = $line['id'];
      $newsletter = new \dana\table\newsletter($id);
      $startdate = $line['publishdate'];
      if ($startdate < $lastdate) {
        self::$recent[$id] = $newsletter;
      }
      self::$list[$id] = $newsletter;
    }
    $result->close();
    return self::$list;
  }

  private function GetDateTime($date, $time) {
    $ret = '';
    if ($date) {
      $ret = date('D, jS F Y', strtotime($date));
      if ($time) {
        $ret .= ' at ' . $time;
      }
    }
    return $ret;
  }

  public function FormatSimple() {
    $title = $this->GetFieldValue('title');
    $link = "scripts/request.php?ty=nl&amp;id=" . $this->ID();
/*
    $location = $this->GetFieldValue('url');
    if ($location) {
      $location .= '<br>';
    }
*/
    return "                    <li><span class=\"fa fa-newspaper-o\"></span><a href=\"{$link}\">{$title}</a></li>";
  }
/*
  private function GetGalleryLink() {
    $ret = false;
    $galleryid = $this->GetFieldValue('galleryid');
    if ($galleryid) {
      require_once 'class.table.gallery.php';
      $gallery = new \dana\table\gallery($galleryid);
      if ($gallery->exists) {
        $url = "gallery.html?ty=ref&amp;ref=" . $gallery->GetFieldValue('ref');
        $ret = \ArrayToString([
          "<div class='gallerylink'>",
          "  <a href='{$url}'>Show Gallery</a>",
          "</div>"
        ]);
      }
    }
    return $ret;
  }

  public function FormatItem($showcontent = true, $level = 2) {
    $start = $this->GetDateTime(
      $this->GetFieldValue('startdate'),
      $this->GetFieldValue('starttime')
    );
    $end = $this->GetDateTime(
      $this->GetFieldValue('enddate'),
      $this->GetFieldValue('endtime')
    );
    if ($start) {
      $eventdate = '<span>' . $start;
      if ($end) {
        $eventdate .= ' to ' . $end;
      }
      $eventdate .= '</span>';
    } else {
      $eventdate = '';
    }
    $locationname = $this->GetFieldValue('locationname');
    $locationaddress = $this->GetFieldValue('locationaddress');
    $location = ($locationname) ?
      "  <p>{$locationname}, {$locationaddress}</p>" : '';
    $title = $this->GetFieldValue('title');
    $content = ($showcontent)
      ? '  <div>' . $this->GetFieldValue('content') . '</div>' : '';
    $gallerylink = $this->GetGalleryLink();
    $ret = [
      '<section class="event">',
      "  <h{$level}>{$title}</h{$level}>", $location, $eventdate, $content, $gallerylink,
      '</section>',''
    ];
    return \ArrayToString($ret);
  }
*/
}

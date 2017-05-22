<?php
namespace dana\table;

require_once 'class.basetable.php';
require_once 'class.table.media.php';

/**
  * article table
  * @version dana framework v.3
*/

class article extends idtable {
  const MODE_RECENT = 'r';
  const MODE_STICKY = 's';
  const MODE_GROUP = 'g';

  const STYLE_HEADING = 'hd';
  const STYLE_PUBDATE = 'pd';
  const STYLE_CLICKABLE = 'clk';
  const STYLE_CONTENT = 'con';
  const STYLE_IMG = 'img';
  const STYLE_CLASS = 'cl';
  const STYLE_OTHERSINGROUP = 'oig';

  static $grouplist = false;

  public $media;

  function __construct($id = 0) {
    if (\is_numeric($id)) {
      parent::__construct('article', $id);
    } else {
      parent::__construct('article', 0);
      !$this->FindByRef($id);
    }
  }

  protected function AssignFields() {
    parent::AssignFields();
    $this->AddField(self::FN_REF, self::DT_REF);
    $this->AddField('articletypeid', self::DT_FK);
    $this->AddField('publishdate', self::DT_DATE);
    $this->AddField('title', self::DT_STRING);
    $this->AddField('mediaid', self::DT_FK);
    $this->AddField('articlegroupid', self::DT_FK);
    $this->AddField('mode', self::DT_STRING);
    $this->AddField('content', self::DT_TEXT);
    $this->AddField(self::FN_STATUS, self::DT_STATUS);
  }

  protected function AfterPopulateFields() {
    $mediaid = $this->GetFieldValue('mediaid');
    $this->media = ($mediaid)
      ? new \dana\table\media($mediaid) : false;
  }

  private function ListAsString($list) {
    return \ArrayToString(array_filter($list));
  }

  private function GetDateTime($date) {
    $ret = '';
    if ($date) {
      $ret = date('D, jS F Y', strtotime($date));
    }
    return $ret;
  }

  private function GetArticlesByGroup($groupid) {
    $currentid = $this->ID();
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $query =
      'SELECT `id` FROM `article` ' .
      "WHERE `status` = '{$status}' AND `articlegroupid` = {$groupid} " .
      'ORDER BY `title`';
    $result = \dana\core\database::Query($query);
    $list = [];
    while ($line = $result->fetch_assoc()) {
      $artid = $line['id'];
      if ($artid != $currentid) {
        $list[$artid] = new \dana\table\article($artid);
      }
    }
    $result->close();
    return $list;
  }

  private function BuildArticlesByGroup($groupid) {
    $script = 'articles.html';
    $group = $this->GetArticlesByGroup($groupid);
    $grouplist = '';
    foreach($group as $grpart) {
      $ref = $grpart->GetFieldValue('ref');
      $title = $grpart->GetFieldValue('title');
      $hint = 'click to read ' . $title;
      $url = $script . '?ty=ref&ref=' . $ref;
      $link = "<a href='{$url}' title='{$hint}'>{$title}</a>";
      $grouplist .= "<li>{$link}</li>";
    }
    $groupsection = [
      "<div class='artgrouplist'>",
        '<p>See Also</p>',
        '<ul>',
          $grouplist,
        '</ul>',
      '</div>'
    ];
    return $this->ListAsString($groupsection);
  }

  static public function GetArticleGroup() {
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $query =
      'SELECT g.* FROM `articlegroup` g ' .
      "WHERE g.`status` = '{$status}' " .
      'ORDER BY g.`ref`';
    $result = \dana\core\database::Query($query);
    $list = [];
    while ($line = $result->fetch_assoc()) {
      $ref = $line['ref'];
      $list[$ref] = $line;
    }
    $result->close();
    return $list;
  }

  static public function GetArticleList($mode, $groupref = false) {
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $clause = "WHERE a.`status` = '{$status}' ";
    $join = 'INNER JOIN `articletype` t ON a.`articletypeid` = t.`id` ';
    $limit = "";
    $sort = 'ORDER BY a.`publishdate`';
    switch ($mode) {
      case self::MODE_RECENT:
        $limit = ' LIMIT 0, 10';
        $clause .= " AND a.`publishdate` > (NOW() - INTERVAL 1 MONTH) AND `mode` = 'N' ";
        break;
      case self::MODE_STICKY:
        $clause .= " AND a.`mode` = 'S' ";
        break;
      case self::MODE_GROUP:
        $clause .= " AND g.`ref` = '{$groupref}' ";
        $join = 'INNER JOIN `articlegroup` g ON a.`articlegroupid` = g.`id` ';
        break;
    }
    $query =
      'SELECT a.`id` FROM `article` a ' .
      $join . $clause . $sort . $limit;
    $result = \dana\core\database::Query($query);
    $list = [];
    while ($line = $result->fetch_assoc()) {
      $id = $line['id'];
      $list[$id] = new \dana\table\article($id);
    }
    $result->close();
    return $list;
  }

  static function FormatGroup($group) {
    $groupref = $group['ref'];
    $groupname = $group['title'];
    $link = "scripts/ajax.fetcharticle.php?ty=group&amp;ref={$groupref}";
    $ret = '<a href="' . $link . '">' . $groupname . '</a>';
    return $ret;
  }

  public function FormatItem($idname = '', $style = []) {
    $idattr = ($idname) ? "id='{$idname}' " : false;
    $title = $this->GetFieldValue('title');
    $artref = $this->GetFieldValue('ref');
    if (\in_array(self::STYLE_CLASS, $style)) {
      $class = " class='4u'>";
    } else {
      $class = '';
    }
    if ($idattr) {
      $start = "  <div {$idattr}class='article'>";
      $end = '  </div>';
      $headlevel = 3;
    } else {
      $start = "  <section id='{$artref}'{$class}>";
      $end = '  </section>';
      $headlevel = 2;
    }
    if (\in_array(self::STYLE_PUBDATE, $style)) {
      $publishdate = $this->GetDateTime($this->GetFieldValue('publishdate'));
      if ($publishdate) {
        $articledate = "<span>{$publishdate}</span>";
      } else {
        $articledate = false;
      }
    } else {
      $articledate = false;
    }
    if (\in_array(self::STYLE_CLICKABLE, $style)) {
      $url = 'articles.html?ty=ref&amp;ref=' . $artref;
      $link = "<a href='{$url}' title='click to read more about {$title}'>{$title}</a>";
    } else {
      $link = $title;
    }
    if (\in_array(self::STYLE_CONTENT, $style)) {
      $content = '  <div>' . $this->GetFieldValue('content') . '</div>';
    } else {
      $content = false;
    }
    if (\in_array(self::STYLE_IMG, $style)) {
      $img = "    <a href='#' class='image feature'><img src='images/pic02.jpg' alt=''></a>";
    } else {
      $img = false;
    }
    if (\in_array(self::STYLE_HEADING, $style)) {
      $heading = "    <hr><h{$headlevel}>{$link}</h{$headlevel}>";
    } else {
      $heading = '';
    }
    if (\in_array(self::STYLE_OTHERSINGROUP, $style)) {
      $groupsection = $this->BuildArticlesByGroup($this->GetFieldValue('articlegroupid'));
    } else {
      $groupsection = '';
    }
    $ret = [$start, $heading, $img, $articledate, $content, $groupsection, $end, ''];
    return $this->ListAsString($ret);
  }

  public function Show($includetoc = false) {
    $tocbtn = ($includetoc)
      ? '<span class="toctopbtn" title="click to back to table of contents"><span class="fa fa-arrow-circle-o-up"></span> TOC</span>'
      : '';
    $backtolist = '<span class="articlelist" title="click to back to article list"><span class="fa fa-list" aria-hidden="true"></span>Article List</span>';
    $title = $this->GetFieldValue('title');
    $content = $this->GetFieldValue('content');
    $ret = [
      "<section class='article'>",
      "  <h3>{$title}</h3>",
      "  <p>{$content}</p>",
      "  <p class='articlebuttons'>{$tocbtn}{$backtolist}</p>",
      '</section>'
    ];
    return \ArrayToString($ret);
  }
}

\dana\table\article::$grouplist = \dana\table\article::GetArticleGroup();

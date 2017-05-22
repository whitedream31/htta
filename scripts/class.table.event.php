<?php
namespace dana\table;

require_once 'class.basetable.php';

/**
  * event table
  * @version dana framework v.3
*/

class event extends idtable {
  public $eventtypedescription;
  public $foregroundcolour;
  public $backgroundcolour;
  public $icon;

  static $list = false;
  static $now;
  static $pastevents = false;
  static $soonevents = false;
  static $futureevents = false;

  function __construct($id = 0) {
    parent::__construct('event', $id);
  }

  protected function AssignFields() {
    parent::AssignFields();
    $this->AddField('eventtypeid', self::DT_FK);
    $this->AddField('startdate', self::DT_DATE);
    $this->AddField('starttime', self::DT_STRING);
    $this->AddField('enddate', self::DT_DATE);
    $this->AddField('endtime', self::DT_STRING);
    $this->AddField('cutoffdate', self::DT_DATE);
    $this->AddField('locationname', self::DT_STRING);
    $this->AddField('locationaddress', self::DT_STRING);
    $this->AddField('title', self::DT_STRING);
    $this->AddField('content', self::DT_TEXT);
    $this->AddField('galleryid', self::DT_FK);
    $this->AddField(self::FN_STATUS, self::DT_STATUS);
  }

  private function GetEventType() {
    $query =
      'SELECT * FROM `eventtype` ' .
      'WHERE `id` = ' . $this->GetFieldValue('eventtypeid');
    $result = \dana\core\database::Query($query);
    $line = $result->fetch_assoc();
    $ret = $line;
    $result->close();
    return $ret;
  }

  protected function AfterPopulateFields() {
    $et = $this->GetEventType();
    $this->eventtypedescription = ($et) ? $et[self::FN_DESCRIPTION] : '';
    $this->foregroundcolour = ($et) ? $et['foregroundcolour'] : '#000000';
    $this->backgroundcolour = ($et) ? $et['backgroundcolour'] : '#000000';
    $this->icon = ($et) ? $et['icon'] : false;
  }

  static function FormatAsItem($events, $caption, $name) {
    $lines = [];
//    $lines[] = "<section class='eventcontainer' id='eventlist'>";
    $lines[] = '  <div class="events">';
    if ($name) {
      $lines[] = "  <a name='{$name}'></a>";
    }
    $lines[] = "  <h2>{$caption}</h2>";
    if ($events) {
//      $lines[] = '  <ul>';
      foreach ($events as $event) {
        $lines[] = $event->FormatItem(true, 3);
      }
//      $lines[] = '  </ul>';
    } else {
      $lines[] = "  <p><i>Sorry, none available at the moment</i></p>";
    }
    $lines[] = '  </div>';
//    $lines[] = '</section>';
    return \ArrayToString($lines);
  }

  // TODO REMOVE SOON
  static public function GetEventList($duration = '+6 months') {
    $now = date('Y-m-d');
    $soon = date('Y-m-d', strtotime($duration));
//    self::$pastevents = [];
    self::$soonevents = [];
//    self::$futureevents = [];
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $query =
      'SELECT e.`id`, e.`startdate` ' .
      'FROM `event` e ' .
      'INNER JOIN `eventtype` t ON e.`eventtypeid` = t.`id` ' .
      "WHERE e.`status` = '{$status}' " .
      'ORDER BY e.`startdate`, e.`starttime`';
    $result = \dana\core\database::Query($query);
    $list = [];
    while ($line = $result->fetch_assoc()) {
      $id = $line['id'];
      $event = new \dana\table\event($id);
      $startdate = $line['startdate'];
      if ($startdate < $now) {
        self::$pastevents[$id] = $event;
      } elseif ($startdate > $soon) {
        self::$futureevents[$id] = $event;
      } else {
        self::$soonevents[$id] = $event;
      }
      $list[$id] = $event;
    }
    $result->close();
    self::$list = $list;
    return $list;
  }

  static public function GetUpcomingEventList($duration = '+6 months') {
    $now = date('Y-m-d');
    $soon = date('Y-m-d', strtotime($duration));
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $query =
      'SELECT e.`id`, e.`startdate` ' .
      'FROM `event` e ' .
      'INNER JOIN `eventtype` t ON e.`eventtypeid` = t.`id` ' .
      "WHERE e.`status` = '{$status}' " .
      'ORDER BY e.`startdate`, e.`starttime`';
    $result = \dana\core\database::Query($query);
    $list = [];
    while ($line = $result->fetch_assoc()) {
      $id = $line['id'];
      $event = new \dana\table\event($id);
      $startdate = $line['startdate'];
      if ($startdate >= $now && $startdate <= $soon) {
        $list[$id] = $event;
      }
    }
    $result->close();
    self::$list = $list;
    return $list;
  }

  static public function GetEventInDate($stamp) {
    $startdate = date('Y-m-d', $stamp);
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $query =
      'SELECT e.`id`, e.`startdate` ' .
      'FROM `event` e ' .
      'INNER JOIN `eventtype` t ON e.`eventtypeid` = t.`id` ' .
      "WHERE e.`status` = '{$status}' " .
      "AND (`startdate` <= '{$startdate}') " .
      "AND ((`enddate` >= '{$startdate}') OR (`enddate` IS NULL)) " .
      'ORDER BY e.`startdate`, e.`starttime`';
    $result = \dana\core\database::Query($query);
    $list = [];
    while ($line = $result->fetch_assoc()) {
      $id = $line['id'];
      $event = new \dana\table\event($id);
      $startdate = $line['startdate'];
      $list[$id] = $event;
    }
    $result->close();
    return $list;
  }

  static public function GetEventInMonth($month, $year) {
    $monthname = date('F', mktime(0, 0, 0, $month, 1, $year));
//    $endstamp = strtotime('+1 month', $startstamp);
    $startstamp = strtotime('1 ' . $monthname . $year);
    $startdate = date('Y-m-d', $startstamp);

//    $startstamp = strtotime('1 ' . date('M Y'));
    $enddate = date('Y-m-d', strtotime('+1 month', $startstamp));
    $status = \dana\table\basetable::STATUS_ACTIVE;
    $query =
      'SELECT e.`id`, e.`startdate` ' .
      'FROM `event` e ' .
      'INNER JOIN `eventtype` t ON e.`eventtypeid` = t.`id` ' .
      "WHERE e.`status` = '{$status}' " .
      "AND (`startdate` >= '{$startdate}') AND (`startdate` < '{$enddate}') " .
      'ORDER BY e.`startdate`, e.`starttime`';
    $result = \dana\core\database::Query($query);
    $list = [];
    while ($line = $result->fetch_assoc()) {
      $id = $line['id'];
      $event = new \dana\table\event($id);
      $startdate = $line['startdate'];
      $list[$id] = $event;
    }
    $result->close();
    return $list;
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

  public function FormatDates() {
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
      if ($end && $start !== $end) {
        $eventdate .= ' to ' . $end;
      }
      $eventdate .= '</span>';
    } else {
      $eventdate = '';
    }
    return $eventdate;
  }

  public function FormatSimple() {
    $title = $this->GetFieldValue('title');
    $icon = $this->icon;
    $fg = $this->foregroundcolour;
    $bg = $this->backgroundcolour;
    $url = 'events.html?ty=id&amp;id=' . $this->ID();
//    $link = "<a href='{$url}' title='click to view more about {$title}'>{$title}</a>";
    $location = $this->GetFieldValue('locationname');
    if ($location) {
      $location .= '<br>';
    }
    $date = $this->FormatDates();
    return "    <li><h4 class='fa fa-{$icon}' style='color:{$fg};background-color:{$bg};padding: 5px'>&nbsp;{$title}</h4><p>{$location}{$date}</p></li>";
  }

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
          "  <a href='{$url}'>Show Gallery</a><br>",
          "</div>"
        ]);
      }
    }
    return $ret;
  }

  private function GetCalendarLink() {
    $ics = "scripts/request.php?ty=ics&amp;id=" . $this->ID();
    $ret = \ArrayToString([
      "<div class='calendarlink'>",
      "  <a href='{$ics}'>Add to your calendar</a>",
      "</div>"
    ]);
    return $ret;
  }

  private function GetInterestedLink() {
    $eventid = $this->ID();
    $interestedplaceholder = \ArrayToString([
      "<div class='interesteddata'>",
      "  <form action='' method='get' class='interestedform'>",
      "    <label>Please enter your e-mail address:</label>",
      "      <input type='email' placeholder='my@email.com'>",
      "    <label>How many people:</label>",
      "      <input type='number' value='2'>",
      "    <input type='hidden' value='{$eventid}'>",
      "    <input class='interestedbutton' type='submit' value='Send'>",
      "  </form>",
      "</div>"
    ]);
    return
      "<a href='#' class='eventinterest' data-id={$eventid}>Interested?</a>" . $interestedplaceholder;
  }

  public function FormatItem($showcontent = true, $level = 2, $showinterest = true) {
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
      if ($end && ($start !== $end)) {
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
    $calendarlink = ($showcontent) ? $this->GetCalendarLink() : '';
    $cutoffdate = $this->GetFieldValue('cutoffdate');
    if ($cutoffdate == '') {
      $cutoffdate = $this->GetFieldValue('enddate');
    }
    $interestinrange = ($cutoffdate > date('Y-m-d'));
    if ($showinterest && $interestinrange) {
      $interest =  $this->GetInterestedLink();
    } else {
      $interest = '';
    }
    $ret = [
      '<section class="event">',
      "  <h{$level}>{$title}</h{$level}>",
      $location, $eventdate, $content, $gallerylink, $calendarlink, $interest,
      '</section>',''
    ];
    return \ArrayToString($ret);
  }

  public function GenerateICS() {
    require_once 'EasyPeasyICS.php';
    $start = strtotime($this->GetDateTime(
      $this->GetFieldValue('startdate'),
      $this->GetFieldValue('starttime')
    ));
    $end = strtotime($this->GetDateTime(
      $this->GetFieldValue('enddate'),
      $this->GetFieldValue('endtime')
    ));
    $summary = $this->GetFieldValue('title');
    $description = $this->GetFieldValue('content');
    $url = '';
    $uid = rand(1000, 99999);
    $filename = htmlspecialchars(str_replace(' ', '', $summary));
    $ics = new \EasyPeasyICS($filename);
    $ics->addEvent($start, $end, $summary, $description, $url, $uid);
    $ics->render(true);
  }
}

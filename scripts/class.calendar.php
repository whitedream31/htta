<?php

class calendarevent {
  public $startdate;
  public $enddate;
  public $days;
  public $title;

  function __construct($startdate, $days, $title) {
    $day = calendar::DAY; //60*60*24;
    $this->startdate = is_string($startdate) ? strtotime($startdate) : $startdate;
    $this->enddate = $this->startdate + (($days-1) * $day);
    $this->days = $days;
    $this->title = $title;
  }

  public function MatchesDay($day) {
    if ($day >= $this->startdate) {
      if ($day <= $this->enddate) {
        $ret = 0; // match
      } else {
        $ret = 1; // too late
      }
    } else {
      $ret = -1; // too early
    }
    return $ret;
  }
}

class calendar {
  const DAY = 86400; // 60*60*24
  public $events = [];
  public $month;
  public $year;
  public $today;
  public $monthurl; // url to use when next/prev month is clicked
  public $cellurl;  // url to use when cell date is clicked
  public $classname = false; // class name for outer div container

  private $monthname;
  private $calstart;
  private $caption;

  private $grid;
  private $prevmonthname;
  private $nextmonthname;
  private $prevmonthnumber;
  private $nextmonthnumber;
  private $prevyear;
  private $nextyear;
  private $endate;
  private $basedate;

  function __construct($calmonth, $calyear, $monthurl = false, $cellurl = false) {
    $this->today = date('d-m-Y');
    $this->month = $calmonth;
    $this->year = $calyear;
    $this->monthurl = ($monthurl) ? $monthurl : 'month.php';
    $this->cellurl = ($cellurl) ? $cellurl : 'viewentry.php';
  }

  private function GetBaseDate() {
    $basedate = $this->calstart;
    while (date('N', $basedate) != 7) {
      $basedate = strtotime('-1 day', $basedate);
    }
    return $basedate;
  }

  private function Prepare() {  
    $this->monthname = date('F', mktime(0, 0, 0, $this->month, 1, $this->year));
    $this->calstart = strtotime('01 ' . $this->monthname . ' ' . $this->year); // the first day of the month
    $prevmonth = strtotime('-1 month', $this->calstart);
    $nextmonth = strtotime('+1 month', $this->calstart);
    $this->prevmonthname = date('F', $prevmonth);
    $this->nextmonthname = date('F', $nextmonth);
    $this->prevmonthnumber = date('n', $prevmonth);
    $this->nextmonthnumber = date('n', $nextmonth);
    $this->prevyear = date('Y', $prevmonth);
    $this->nextyear = date('Y', $nextmonth);
    $this->endate = strtotime('+1 month', $this->calstart); // the end of the month (day after actually!)
    $this->caption = $this->monthname . ' ' . $this->year;
    // find the first day in the calendar (last sunday of the previous month)
    $this->basedate = $this->GetBaseDate();
  }

  private function GetMonthDropList($month) {
  //  $month = date('n');
    $ret = ['<select id="monthselection" name="m">'];
    for($lp = 1; $lp <= 12; $lp++) {
      $monthname = date('F', mktime(0, 0, 0, $lp, 1, 2017));
      $selected = ($lp == $month) ? 'selected' : '';
      $ret[] = "<option value='{$lp}' $selected>{$monthname}</option>";
    }
    $ret[] = '</select>';
    return implode(PHP_EOL, $ret);
  }

  private function GetYearDropList($year) {
    $ret = ['<select id="yearselection" name="y">'];
    for($lp = 2015; $lp <= 2030; $lp++) {
      $selected = ($lp == $year) ? 'selected' : '';
      $ret[] = "<option value='{$lp}' $selected>{$lp}</option>";
    }
    $ret[] = '</select>';
    return implode(PHP_EOL, $ret);
  }

  private function SortEvents() {
    usort($this->events, function($a, $b) {
      return strcmp($a->startdate, $b->startdate);
    });
  }

  private function BuildTable() {
    $url = $this->monthurl;
    $monthlist = $this->GetMonthDropList($this->month);
    $yearlist = $this->GetYearDropList($this->year);
    $class = ($this->classname) ? " class='{$this->classname}'" : '';
    $ret = [
      "<div{$class}>",
      '  <table class="calendar" summary="Calendar">',
      "    <caption>{$monthlist}{$yearlist}</caption>",
      "    <thead>",
      "      <tr>",
      '        <th abbr="Sunday" scope="col" title="Sunday">S</th>',
      '        <th abbr="Monday" scope="col" title="Monday">M</th>',
      '        <th abbr="Tuesday" scope="col" title="Tuesday">T</th>',
      '        <th abbr="Wednesday" scope="col" title="Wednesday">W</th>',
      '        <th abbr="Thursday" scope="col" title="Thursday">T</th>',
      '        <th abbr="Friday" scope="col" title="Friday">F</th>',
      '        <th abbr="Saturday" scope="col" title="Saturday">S</th>',
      '      </tr>',
      '    </thead>',
      '    <tfoot>',
      '      <tr>',
      "        <td abbr='{$this->prevmonthname}' colspan='3' class='calbtnprev'>" .
      "          <a href='#' class='calbtn' " .
//      "<a href='{$url}?m={$this->prevmonthnumber}&y={$this->prevyear}' " .
      "data-month={$this->prevmonthnumber} data-year={$this->prevyear} " .
      "title='previous month'>&laquo; {$this->prevmonthname}</a>" .
      '</td>',
      '        <td class="pad">&nbsp;</td>',
      "        <td abbr='{$this->nextmonthname}' colspan='3' class='calbtnnext'>" .
      "          <a href='#' class='calbtn' " .
//      "          <a href='{$url}?m={$this->nextmonthnumber}&y={$this->nextyear}' " .
      "data-month={$this->nextmonthnumber} data-year={$this->nextyear} " .
      "title='next month'>{$this->nextmonthname} &raquo;</a>" .
               '</td>',
      '      </tr>',
      '    </tfoot>'
    ];
    foreach($this->grid as $row) {
      $ret[] = '  <tr>';
      foreach($row as $cell) {
        $ret[] = '    ' . $cell;
      }
      $ret[] = '  </tr>';
    }
    $ret[] = '    <tbody>';
    $ret[] = '  </table>';
    $ret[] = '</div>';

    return implode(PHP_EOL, $ret);
  }

  private function GetEventsForMonth() {
    $daydiff = self::DAY; //60*60*24
    $this->SortEvents();
    $monthevents = $this->events;
    $start = $this->calstart;
    $end = $this->endate;
    $ret = [];
    $day = $start;
    while($day <= $end) {
      $dayevents = $this->FindEvents($day, $monthevents);
      foreach($dayevents as $events) {
        foreach ($events as $event) {
          $ret[] = $event;
        }
      }
      $day += $daydiff;
    }
    return $ret;
  }

  public function AddEvent($startdate, $title, $days = 1) {
    $this->events[] = new calendarevent($startdate, $days, $title);
//echo "<p>FOUND: " . date('d F Y', $startdate) . ' - ' . $title . "</p>";
  }

  private function FindEvents($day, $events) {
    $ret = [];
    foreach($events as $event) {
      if ($event->MatchesDay($day) == 0) {
        $ret[$day][] = $event;
      }
//echo "<p>FOUND: " . date('d F Y', $event->startdate) . ' - ' . $event->title . ' - ' . $dayname . ': ' . $msg . "</p>";
    }
    return $ret;
  }

  private function BuildEventTitle($eventinday) {
    $ret = [];
    $prev = false;
    foreach($eventinday as $eventlist) {
      foreach($eventlist as $event) {
        $title = $event->title;
//        if ($event !== $prev) {
          $ret[$title] = $event->title;
//          $prev = $event;
//        }
      }
    }
    return implode(", ", $ret);
  }

  public function ShowCalendar($outputasarray = false) {
    $this->Prepare();
    $url = $this->cellurl;
    $events = $this->GetEventsForMonth();
//    $nextevent = $events ? array_shift($events) : false;
    // display the cell day values
    $celltitle = '';
    $today = strtotime($this->today);
    $loopdate = $this->basedate;
    $day = 1;
    $istoday = false;
    $this->grid = [];
    while ($loopdate < $this->endate) {
      $rowout = [];
      for ($lp=1; $lp<=7; $lp++) {
        if (date('m', $loopdate) == $this->month) { // same month? then process the day cell
          $pastdate = ($loopdate < $today);
          $istoday = ($loopdate == $today);
          $found = false;
          if ($events) {
            $foundevents = $this->FindEvents($loopdate, $events);
            if ($foundevents) {
              $found = true;
              $celltitle = $this->BuildEventTitle($foundevents);
            }
          }
          if ($found) {
            $cell = "<a href='{$url}?d={$day}&m={$this->month}&y={$this->year}' title='{$celltitle}'>{$day}</a>";
            if ($istoday) {
              $cellclass = 'cellentryhittoday'; // has entries and is today
            } elseif ($pastdate) {
              $cellclass = 'cellentryhitpast'; // has entries but in the past
            } else {
              $cellclass = 'cellentryhitpost'; // has entries but yet to come
            }
            $data = " data-id={$loopdate}";
          } else {
            // no entries found
            $data = '';
            $celltitle = '';
            $cell = $day;
            if ($pastdate) {
              $cellclass = 'cellentrypast'; // no entries and in the past
            } else {
              if ($istoday) {
                $cellclass = 'cellentrytoday'; // no entries but is today
              } else {
                $cellclass = 'cellentrypost'; // no entries but yet to come
              }
            }
          }
          $day++;
        } else {
          $data = '';
          $cell = '&nbsp;';
          $cellclass = 'cellentryblank';
        }
        $rowout[] = "<td class='{$cellclass}'{$data}>{$cell}</td>";
        // go to next day
        $loopdate = strtotime('+1 day', $loopdate);
      }
      $this->grid[] = $rowout;
    }
    $output = $this->BuildTable();
    if ($outputasarray) {
      $ret = $output;
    } else {
      $ret = '';
      echo $output;
    }
    return $ret;
  }
}

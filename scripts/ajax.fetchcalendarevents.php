<?php
namespace dana\display;

require_once 'class.calendar.php';
require_once 'class.table.event.php';

class eventprocessor {
  private $events = [];
  private $month;
  private $year;

  function __construct() {
    $this->month = GetGet('m');
    $this->year = GetGet('y');
    if (!in_array($this->month, range(1, 12))) {
      $this->month = date('n');
    }
    if (!in_array($this->year, range(2015, 2030))) {
      $this->year = date('Y');
    }
  }

  private function GetEvents($month, $year) {
    return \dana\table\event::GetEventInMonth($month, $year);
  }

  private function AddEvent($startdate, $enddate, $title, $cal) {
    $startdatestamp = strtotime($startdate);
    $enddatestamp = strtotime($enddate);
    $datediff = ($enddatestamp - $startdatestamp);
    $days = ceil($datediff / \calendar::DAY) + 1;
    $cal->AddEvent($startdatestamp, $title, $days);
  }

  private function GetCalendars($cal, $month, $year) {
    $dates = $this->GetEvents($month, $year);
    $this->events = [];
    foreach ($dates as $event) {
      $this->AddEvent(
        $event->GetFieldValue('startdate'),
        $event->GetFieldValue('enddate'),
        $event->GetFieldValue('title'), $cal
      );
      $this->events[] = $event;
    }
  }

  private function GetEventList() {
/*
    $retstart = [
      "<section class='eventcontainer' id='eventlist'>",
      "  <ul>"
    ];
    $list = [];
    foreach ($this->events as $event) {
      $list[] = $event->FormatSimple() . PHP_EOL;
    }
    $retend = [
      "  </ul>",
      "</section>",
      "<div style='float: none'></div>"
    ];
*/
    return
      "<section class='eventcontainer' id='eventlist'>" .
      \dana\table\event::FormatAsItem($this->events, 'Events', 'events') .
      "</section>";
//    return implode(PHP_EOL, array_merge($retstart, $list, $retend));
  }

  private function GetSummaryList($caption, $duration) {
    $events = \dana\table\event::GetUpcomingEventList($duration);
    $ret = ['<section id="eventsummary">', "<h2>{$caption}</h2>"];
    foreach ($events as $event) {
      $eventid = $event->ID();
      $ret[] = $event->FormatItem(false, 3, true);
    }
    $ret[] = '</section>';
    $ret[] = '<p><a href="#" id="hidesummary">Hide Summary</a></p>';
    $ret[] = '<p><a href="#" id="showsummary">Show Summary</a></p>';
    return implode(PHP_EOL, $ret);
  }

  public function BuildCalendars() {
    $cal = new \calendar($this->month, $this->year, 'events.html', 'events.html');
    $cal->classname = 'calcontainer';
    $this->GetCalendars($cal, $this->month, $this->year);
    return implode(PHP_EOL, [
//      '<form>',
      $this->GetSummaryList('Upcoming Events', '+6 months'),
      $cal->ShowCalendar(true),
      $this->GetEventList(),
//      '</form>'
    ]);
  }
}

$eventprocessor = new eventprocessor();

echo $eventprocessor->BuildCalendars();

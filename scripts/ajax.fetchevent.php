<?php
namespace dana\display;

require_once 'class.table.event.php';

function FormatAsList($events) {
  $ret = [];
  if ($events) {
    foreach ($events as $event) {
      $ret[] = $event->FormatSimple();
    }
    $ret = "<ul>" . \PHP_EOL . \ArrayToString($ret) . \PHP_EOL . "</ul>";
  } else {
    $ret = "<p><i>Sorry, none available at the moment</i></p>";
  }
  return $ret;
}

function GetPastDates() {
  return \dana\table\event::$pastevents;
}

function GetSoonDates() {
  return \dana\table\event::$soonevents;
}

function GetFutureDates() {
  return \dana\table\event::$futureevents;
}

function GetDates() {
  \dana\table\event::GetEventList();
  $ret['status'] = 'ok';
  $ret['msg'] = '';
  $ret['past'] = FormatAsList(GetPastDates());
  $ret['soon'] = FormatAsList(GetSoonDates());
  $ret['future'] = FormatAsList(GetFutureDates());
  echo \json_encode($ret);
}

function GetPastEvents() {
  \dana\table\event::GetEventList();
  $ret = \dana\table\event::FormatAsItem(GetPastDates(), 'Past Events', 'past');
  return $ret;
}

function GetSoonEvents() {
  \dana\table\event::GetEventList();
  $ret = \dana\table\event::FormatAsItem(GetSoonDates(), 'Events Coming Up', 'soon');
  return $ret;
}

function GetFutureEvents() {
  \dana\table\event::GetEventList();
  $ret = \dana\table\event::FormatAsItem(GetFutureDates(), 'Future Events', 'future');
  return $ret;
}

function GetQueryRef($ref) {
  switch ($ref) {
    case 'past':
      $ret = \dana\display\GetPastEvents();
      break;
    case 'soon':
      $ret = \dana\display\GetSoonEvents();
      break;
    case 'future':
      $ret = \dana\display\GetFutureEvents();
      break;
    default:
      $ret = "<p>Reference '{$ref}' not found</p>";
  }
  return $ret;
}

function DoQuery() {
  $ret = [];
  $keys = \html_entity_decode(GetGet('keys'));
  $list = \json_decode($keys, true);
  if ($list && json_last_error() == JSON_ERROR_NONE) {
    $ty = isset($list['ty']) ? $list['ty'] : false;
    switch($ty) {
      case 'key':
        $ref = isset($list['ref']) ? $list['ref'] : false;
        $ret['content'] = GetQueryRef($ref);
        $ret['anchor'] = 'main';

        break;
      default:
        $ret['content'] = '<p>Key not found</p>';
        $ret['anchor'] = $ty;
    }
  }
  echo \json_encode($ret);
}

function GetCalDate() {
  $stamp = GetGet('stamp');
  $ret['status'] = 'ok';
  $events = \dana\table\event::GetEventInDate($stamp);
  $msg = \dana\table\event::FormatAsItem($events, 'Events for ' . date('D, jS F Y', $stamp), 'caldate');
  $ret['msg'] = $msg;
  echo \json_encode($ret);
}

// main
$artty = GetGet('ty');

switch($artty) {
  case 'caldate':
    \dana\display\GetCalDate();
    break;
  case 'dates':
    \dana\display\GetDates();
    break;
  case 'query':
    \dana\display\DoQuery();
    break;
  case 'past':
    echo \json_encode(['dates' => \dana\display\GetPastEvents()]);
    break;
  case 'soon':
    echo \json_encode(['dates' => \dana\display\GetSoonEvents()]);
    break;
  case 'future':
    echo \json_encode(['dates' => \dana\display\GetFutureEvents()]);
    break;
}

exit;

<?php
namespace dana\display;

require_once 'class.table.newsletter.php';

// functions

function FormatAsList($list) {
  $ret = [];
  if ($list) {
    foreach ($list as $newsletter) {
      $ret[] = $newsletter->FormatSimple();
    }
    $ret = \ArrayToString($ret);
  } else {
    $ret = "<p><i>Sorry, none available at the moment</i></p>";
  }
  return $ret;
}

function GetNewsletterList() {
  $ret['msg'] = '';
  try {
    \dana\table\newsletter::GetNewsletterList('3 months');
    $list = \dana\table\newsletter::$recent;
    $ret['status'] = 'ok';
    $ret['list'] = FormatAsList($list) . PHP_EOL;
  } catch (Exception $e) {
    $ret['status'] = 'error';
    $ret['list'] = "Error: " . $e->getMessage();
  }
  echo \json_encode($ret);
}

function BuildArticleItems($ref) {
  $list = [];
  $list[] = '  <a name="content"></a>';
  $list[] = '  <div id="links">';
  $articlegroup = new \dana\table\articlegroup();
  if ($articlegroup->FindByRef($ref)) {
    $title = $articlegroup->GetFieldValue('title');
    $list[] = "<h2>{$title}</h2>";
    $items = $articlegroup->GetArticleItems($ref);
    if ($items) {
      foreach($items as $item) {
        $list[] = $item->Show();
      }
    } else {
      $list[] = '<p>None found</p>';
    }
    $list[] = "<p id='return' class='button'>Article List</p>";
  }
  $list[] = '</div>';
  return \ArrayToString($list) . PHP_EOL;
}

function GetArticleItems($ref) {
  $ret['status'] = 'ok';
  $ret['msg'] = $ref;
  $ret['list'] = BuildArticleItems($ref);
  echo \json_encode($ret);
}

function GetQueryRef($ref) {
  return \dana\display\BuildArticleItems($ref);
}

function DoQuery() {
  $ret = [];
  $keys = \html_entity_decode(GetGet('keys'));
  $list = \json_decode($keys, true);
  if ($list && json_last_error() == JSON_ERROR_NONE) {
    $ty = isset($list['ty']) ? $list['ty'] : false;
    switch($ty) {
      case 'ref':
        $ref = isset($list['ref']) ? $list['ref'] : false;
        $ret['content'] = GetQueryRef($ref);
        $ret['anchor'] = 'content';

        break;
      default:
        $ret['content'] = "<p>Key '{$ty}' not found</p>";
        $ret['anchor'] = $ty;
    }
  }
  echo \json_encode($ret);
}

// main
$ty = GetGet('ty');

switch($ty) {
  case 'list':
    \dana\display\GetNewsletterList();
    break;
/*
  case 'ref':
    $ref = GetGet('ref');
    \dana\display\GetArticleItems($ref);
    break;
  case 'query':
    \dana\display\DoQuery();
    break;
*/
  default:
    echo \json_encode([
      'status' => 'error',
      'msg' => 'unknown request type ' . $ty
    ]);
}

exit;

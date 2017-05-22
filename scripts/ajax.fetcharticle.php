<?php
namespace dana\display;

require_once 'class.table.article.php';

$articlegroup = false;

// functions

function FormatAsList($list) {
  $ret = [];
  if ($list) {
    foreach ($list as $articlegroup) {
      $ret[] = $articlegroup->FormatSimple();
    }
    $ret = \ArrayToString($ret);
  } else {
    $ret = "<p><i>Sorry, none available at the moment</i></p>";
  }
  return $ret;
}

function GetArticleList() {
  $list = \dana\table\articlegroup::GetGroupList();
  $ret['status'] = 'ok';
  $ret['msg'] = '';
  $ret['list'] = FormatAsList($list) . PHP_EOL;
  echo \json_encode($ret);
}

function BuildArticleItems($ref) {
  global $articlegroup;
  $list = [];
//  $list[] = '  <a name="content"></a>';
  $list[] = '  <div id="links">';
  $articlegroup = new \dana\table\articlegroup();
  if ($articlegroup->FindByRef($ref)) {
    $title = $articlegroup->GetFieldValue('title');
    $list[] = "<h2>{$title}</h2>";
    $list[] = '    <a name="toc"></a>';
    $list[] = '    <div id="toc"></div>';
    $items = $articlegroup->GetArticleItems($ref);
    $showtoc = count($items) > 1;
    if ($items) {
      foreach($items as $item) {
        $list[] = $item->Show($showtoc);
      }
    } else {
      $list[] = '<p>None found</p>';
    }
//    $list[] = '<span class="fa fa-list" aria-hidden="true">Article List</span>';
  } else {
    $list[] = "<p>Ref {$ref} not found</p>";
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
  global $articlegroup;
  $ret = [];
  $keys = \html_entity_decode(GetGet('keys'));
  $list = \json_decode($keys, true);
  if ($list && json_last_error() == JSON_ERROR_NONE) {
    $ty = isset($list['ty']) ? $list['ty'] : false;
    switch($ty) {
      case 'ref':
        $ref = isset($list['ref']) ? $list['ref'] : false;
        $content = GetQueryRef($ref);
        if ($articlegroup) {
          $title = $articlegroup->GetFieldValue('title');
          $byline = $articlegroup->GetFieldValue('info');
        } else {
          $title = 'No Articles Found';
          $byline = '';
        }
        $ret['content'] = $content;
        $ret['anchor'] = 'articlelist';
        $ret['caption'] = $title;
        $ret['byline'] = $byline;
        $ret['anchor'] = 'articlestories';
        break;
      default:
        $ret['content'] = "<p>Key '{$ty}' not found</p>";
        $ret['anchor'] = $ty;
    }
  } else {
    $ret['content'] = "<p>Request: {$keys} not understood</p>";
    $ret['anchor'] = '';
  }
  echo \json_encode($ret);
}

// main
$ty = GetGet('ty');

switch($ty) {
  case 'list':
    \dana\display\GetArticleList();
    break;
  case 'ref':
    $ref = GetGet('ref');
    \dana\display\GetArticleItems($ref);
    break;
  case 'query':
    \dana\display\DoQuery();
    break;
  default:
    echo \json_encode([
      'status' => 'error',
      'msg' => 'unknown request type ' . $ty
    ]);
}

exit;

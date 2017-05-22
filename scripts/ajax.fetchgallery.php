<?php
namespace dana\display;

require_once 'class.table.gallery.php';

function FormatAsList($list) {
  $ret = [];
  $flg = 0;
  if ($list) {
    $ret[] = '<table>';
    foreach ($list as $gallery) {
      switch ($flg % 2) {
        case 0:
          $ret[] = '<tr>';
          break;
        case 2:
          $ret[] = '</tr>';
          break;
      }
      $ret[] = '<td>' . $gallery->FormatSimple() . '</td>';
      $flg++;
    }
    $ret[] = '</table>';
    $ret = \ArrayToString($ret);
  } else {
    $ret = "<p><i>Sorry, none available at the moment</i></p>";
  }
  return $ret;
}

function FormatReturnLink() {
  $ret = [];
  $ret[] = '<div id="return"><a href="#" title="Click to return to the gallery list">Back to Galleries</a></div>';
//  $ret[] = '<script id="#return"><a href="#" title="Click to return to the gallery list">Back to Galleries</a></div>';
  return \ArrayToString($ret);
}

function GetGalleryList() {
  $list = \dana\table\gallery::GetGroupList();
  $ret['status'] = 'ok';
  $ret['msg'] = '';
  $ret['list'] = FormatAsList($list) . PHP_EOL;
  echo \json_encode($ret);
}

function BuildGalleryItems($ref) {
  $list = [];
  $list[] = '  <a name="content"></a>';
  $list[] = '  <div id="links">';
  $gallery = new \dana\table\gallery();
  if ($gallery->FindByRef($ref)) {
    $title = $gallery->GetFieldValue('title');
    $list[] = "<h3>{$title}</h3>";
    $items = $gallery->GetGalleryItems($ref);
//    $items = \dana\table\gallery::GetGalleryItems($ref);
    if ($items) {
      foreach($items as $item) {
        $list[] = $item->Show("images/{$ref}/");
      }
    } else {
      $list[] = '<p>None found</p>';
    }
  }
  $list[] = '</div>';
  return \ArrayToString($list) . PHP_EOL . FormatReturnLink();
}

function GetGalleryItems($ref) {
  $ret['status'] = 'ok';
  $ret['msg'] = $ref;
  $ret['list'] = BuildGalleryItems($ref);
  echo \json_encode($ret);
}

function GetQueryRef($ref) {
  return \dana\display\BuildGalleryItems($ref);
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
    \dana\display\GetGalleryList();
    break;
  case 'ref':
    $ref = GetGet('ref');
    \dana\display\GetGalleryItems($ref);
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

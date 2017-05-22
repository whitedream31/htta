<?php
namespace dana\display;

require_once 'class.table.article.php';

function FormatAsList($list) {
  $ret = [];
  if ($list) {
    foreach ($list as $article) {
      $ret[] = $article->FormatSimple();
    }
    $ret = \ArrayToString($ret);
  } else {
    $ret = "<p><i>Sorry, none available at the moment</i></p>";
  }
  return $ret;
}

function FormatReturnLink() {
  $ret = [];
  $ret[] = '<div id="return"><a href="#top" title="Click to return to the top">Top</a></div>';
  return \ArrayToString($ret);
}

function GetArticleList() {
  $list = \dana\table\article::GetGroupList();
  $ret['status'] = 'ok';
  $ret['msg'] = '';
  $ret['list'] = FormatAsList($list) . PHP_EOL;
  echo \json_encode($ret);
}

function BuildArticleItems($ref) {
  $list = [];
  $list[] = '  <a name="content"></a>';
  $list[] = '  <div id="links">';
  $article = new \dana\table\article();
  if ($article->FindByRef($ref)) {
    $list[] = $article->Show();
  } else {
    $list[] = "    <p>Ref {$ref} not found</p>";
  }
  $list[] = '</div>';
  return \ArrayToString($list) . PHP_EOL . FormatReturnLink();
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
        $ref = isset($list['ref']) ? $list['ref'] : 'na';
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

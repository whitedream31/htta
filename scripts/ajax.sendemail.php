<?php
namespace dana\display;

require_once 'class.table.contact.php';

// functions

/*
function StripUnwantedTagsAndAttrs($html_str) {
  $xml = new DOMDocument();
  libxml_use_internal_errors(true);
// list the tags you want to allow here, NOTE you MUST allow html and body otherwise entire string will be cleared
  $allowed_tags = []; //array("html", "body", "b", "br", "em", "hr", "i", "li", "ol", "p", "s", "span", "table", "tr", "td", "u", "ul");
// list the attributes you want to allow here
  $allowed_attrs = []; //array ("class", "id", "style");
  if (strlen($html_str)) {
    if ($xml->loadHTML($html_str, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
      foreach ($xml->getElementsByTagName("*") as $tag) {
        if (!in_array($tag->tagName, $allowed_tags)){
          $tag->parentNode->removeChild($tag);
        } else {
          foreach ($tag->attributes as $attr) {
            if (!in_array($attr->nodeName, $allowed_attrs)) {
              $tag->removeAttribute($attr->nodeName);
            }
          }
        }
      }
    }
    $ret = $xml->saveHTML();
  } else {
    $ret = false;
  }
  return $ret;
}
*/

function SendEmailMessage($displayname, $email, $subject, $message) {
/*  echo
    "<p>DISPLAYNAME: {$displayname}</p>" .
    "<p>EMAIL: {$email}</p>" .
    "<p>SUBJECT: {$subject}</p>" .
    "<p>MESSAGE:</p><pre>{$message}</pre>\n"; */
  $contact = new \dana\table\contact();
  $contact->SetFieldValue('displayname', $displayname);
  $contact->SetFieldValue('email', $email);
  $contact->SetFieldValue('subject', $subject);
  $contact->SetFieldValue('message', $message);
  $status = $contact->StoreChanges();
  return ($status == -2) ? 'ok' : 'err';
}

function StripTags($str, $allowable_tags = '', $strip_attrs = false, $preserve_comments = false, callable $callback = null) {
  $allowable_tags = array_map('strtolower',
    array_filter( // lowercase
      preg_split('/(?:>|^)\\s*(?:<|$)/', $allowable_tags, -1, PREG_SPLIT_NO_EMPTY), // get tag names
      function($tag) {
        return preg_match('/^[a-z][a-z0-9_]*$/i', $tag);
      } // filter broken
    )
  );
  $comments_and_stuff = preg_split('/(<!--.*?(?:-->|$))/', $str, -1, PREG_SPLIT_DELIM_CAPTURE);
  foreach ($comments_and_stuff as $i => $comment_or_stuff) {
    if ($i % 2) { // html comment
      if (!($preserve_comments && preg_match('/<!--.*?-->/', $comment_or_stuff))) {
        $comments_and_stuff[$i] = '';
      }
    } else { // stuff between comments
      $tags_and_text = preg_split("/(<(?:[^>\"']++|\"[^\"]*+(?:\"|$)|'[^']*+(?:'|$))*(?:>|$))/", $comment_or_stuff, -1, PREG_SPLIT_DELIM_CAPTURE);
      foreach ($tags_and_text as $j => $tag_or_text) {
        $is_broken = false;
        $is_allowable = true;
        $result = $tag_or_text;
        if ($j % 2) { // tag
          if (preg_match("%^(</?)([a-z][a-z0-9_]*)\\b(?:[^>\"'/]++|/+?|\"[^\"]*\"|'[^']*')*?(/?>)%i", $tag_or_text, $matches)) {
            $tag = strtolower($matches[2]);
            if (in_array($tag, $allowable_tags)) {
              if ($strip_attrs) {
                $opening = $matches[1];
                $closing = ($opening === '</') ? '>' : $closing;
                $result = $opening . $tag . $closing;
              }
            } else {
              $is_allowable = false;
              $result = '';
            }
          } else {
            $is_broken = true;
            $result = '';
          }
        } else { // text
          $tag = false;
        }
        if (!$is_broken && isset($callback)) {
          // allow result modification
          call_user_func_array($callback, array(&$result, $tag_or_text, $tag, $is_allowable));
        }
        $tags_and_text[$j] = $result;
      }
      $comments_and_stuff[$i] = implode('', $tags_and_text);
    }
  }
  $str = implode('', $comments_and_stuff);
  return $str;
}

function DoProcess() {
  $ret = [];
  $displayname = GetGet('displayname');
  $subject = GetGet('subject');
  $email = GetGet('email');
  $message = StripTags(GetGet('message'));
  if (!empty($email) && !empty($message)) {
    try {
      $status = SendEmailMessage($displayname, $email, $subject, $message);
      if ($status == 'ok') {
        $content = 'Your message was sent! Thank you.';
      } else {
        $content = 'Could not send email - please try again later';
      }
    } catch (Exception $e) {
      $content = 'Could not send email - please try again later';
      $status = 'err';
    }
    $ret['status'] = $status;
    $ret['content'] = $content;
    
  } else {
    $ret['status'] = 'err';
    $ret['content'] = 'PLEASE CHECK YOUR MESSAGE / EMAIL AND TRY AGAIN';
  }
  return $ret;
}

// main

$ret = DoProcess();
echo \json_encode($ret);

exit;

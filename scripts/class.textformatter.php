<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace dana\activity;

/**
 * Description of class
 *
 * @author white
 */
class textformatter {
  const MARKER_START = '[!';
  const MARKER_END = '!]';

  const FLAG_STRONG = 1;
  const FLAG_ITALIC = 2;
  const FLAG_LIST = 3;
  const FLAG_HEADING = 4;
  const FLAG_SUBHEADING = 4;

  const FLAGATTR_TAG = 't';
  const FLAGATTR_VALUE = 'v';
  const FLAGATTR_ELEMENT = 'e';
  const FLAGATTR_ISPAR = 'p';

  const TAG_STRONG = 'strong';
  const TAG_ITALIC = 'italic';
  const TAG_LIST = 'list';
  const TAG_HEADING = 'heading';
  const TAG_SUBHEADING = 'subheading';

  private $flags = [];
  private $text = '';
  private $value;
  private $inlist;

  function __construct($value) {
    $this->value = $value;
    $this->flags = [
      self::FLAG_STRONG =>
        [self::FLAGATTR_TAG => self::TAG_STRONG, self::FLAGATTR_ELEMENT => 'strong', self::FLAGATTR_VALUE => 0, self::FLAGATTR_ISPAR => 0],
      self::FLAG_ITALIC =>
        [self::FLAGATTR_TAG => self::TAG_ITALIC, self::FLAGATTR_ELEMENT => 'em', self::FLAGATTR_VALUE => 0, self::FLAGATTR_ISPAR => 0],
      self::FLAG_LIST =>
        [self::FLAGATTR_TAG => self::TAG_LIST, self::FLAGATTR_ELEMENT => 'ul', self::FLAGATTR_VALUE => 0, self::FLAGATTR_ISPAR => 1],
      self::FLAG_HEADING =>
        [self::FLAGATTR_TAG => self::TAG_HEADING, self::FLAGATTR_ELEMENT => 'h2', self::FLAGATTR_VALUE => 0, self::FLAGATTR_ISPAR => 1],
      self::FLAG_SUBHEADING =>
        [self::FLAGATTR_TAG => self::TAG_SUBHEADING, self::FLAGATTR_ELEMENT => 'h3', self::FLAGATTR_VALUE => 0, self::FLAGATTR_ISPAR => 1]
    ];
  }

  private function GetParagraphs($value) {
    $delim = '';
    if (\strpos($value, '&#13;')) {
      $delim = '&#13;';
    }
    if (\strpos($value, '&#10;')) {
      $delim .= '&#10;';
    }
    $ret = ($delim) ? \explode($delim, $value) : [$value];
    return $ret;
  }

  private function MarkWithTag($flag) {
    $v = $flag[self::FLAGATTR_VALUE];
    $e = $flag[self::FLAGATTR_ELEMENT];
//    $this->text .= (($v) ? "<{$e}>" : "</{$e}>");
    return (($v) ? "<{$e}>" : "</{$e}>");
  }

  private function ProcessTag($tag) {
    switch ($tag) {
      case self::TAG_STRONG:
        $this->flags[self::FLAG_STRONG][self::FLAGATTR_VALUE] = !$this->flags[self::FLAG_STRONG][self::FLAGATTR_VALUE];
        $ret = $this->MarkWithTag($this->flags[self::FLAG_STRONG]);
        break;
      case self::TAG_ITALIC:
        $this->flags[self::FLAG_ITALIC][self::FLAGATTR_VALUE] = !$this->flags[self::FLAG_ITALIC][self::FLAGATTR_VALUE];
        $ret = $this->MarkWithTag($this->flags[self::FLAG_ITALIC]);
        break;
      case self::TAG_LIST:
        $this->flags[self::FLAG_LIST][self::FLAGATTR_VALUE] = !$this->flags[self::FLAG_LIST][self::FLAGATTR_VALUE];
        $ret = $this->MarkWithTag($this->flags[self::FLAG_LIST]);
//        $this->inlist = !$this->inlist;
        break;
      case self::TAG_HEADING:
        $this->flags[self::FLAG_HEADING][self::FLAGATTR_VALUE] = !$this->flags[self::FLAG_ITALIC][self::FLAGATTR_VALUE];
        $ret = $this->MarkWithTag($this->flags[self::FLAG_HEADING]);
        break;
      case self::TAG_SUBHEADING:
        $this->flags[self::FLAG_SUBHEADING][self::FLAGATTR_VALUE] = !$this->flags[self::FLAG_ITALIC][self::FLAGATTR_VALUE];
        $ret = $this->MarkWithTag($this->flags[self::FLAG_SUBHEADING]);
        break;
      default:
        $ret = '';
    }
    return $ret;
  }

  private function CheckOpenFlags() {
    foreach($this->flags as $flgid => $flgval) {
      $v = $flgval[self::FLAGATTR_VALUE];
      if ($v) {
        $this->MarkWithTag($this->flags[$flgid]);
      }
    }
  }

  private function HasParagraphTag($ln) {
    $exists = false;
    foreach($this->flags as $flgval) {
      if ($flgval[self::FLAGATTR_ISPAR]) {
        $tag = self::MARKER_START . $flgval[self::FLAGATTR_TAG] . self::MARKER_END;
        $exists = $exists || \strpos($ln, $tag) === 0;
      }
    }
    return $exists;
  }

  private function ProcessLine($ln) {
    $haspartag = $this->HasParagraphTag($ln);
    //$this->text .= ($haspartag || $this->inlist) ? '' : '<p>';
    $ret = ($haspartag || $this->inlist) ? '' : '<p>';
    $offsetstart = \strlen(self::MARKER_START);
    $offsetend = \strlen(self::MARKER_END);
    do {
      $posstart = strpos($ln, self::MARKER_START);
      if ($posstart !== false) {
        $posend = \strpos($ln, self::MARKER_END, $posstart);
        if ($posend !== false) {
          $len = $posend - $posstart - $offsetstart;
          $tag = \substr($ln, $posstart + $offsetstart, $len);
//          $this->text .= \substr($ln, 0, $posstart);
          $ret .= \substr($ln, 0, $posstart);
          $ret .= $this->ProcessTag($tag);
          $ln = \substr($ln, $posend + $offsetend);
        }
      } else {
//        $this->text .= $ln;
        $ret .= $ln;
        $posstart = false;
      }
    } while ($posstart !== false);
//    $this->text .= ($haspartag || $this->inlist) ? '' : '</p>' . PHP_EOL;
    $ret .= ($haspartag || $this->inlist) ? '' : '</p>' . PHP_EOL;
    return $ret;
  }

  function __toString() {
    $this->text = '';
    $this->inlist = false;
    $pars = $this->GetParagraphs($this->value);
    foreach ($pars as $ln) {
      if (trim($ln)) {
        $this->inlist = $this->flags[self::FLAG_LIST][self::FLAGATTR_VALUE];
//        $this->text .= ($this->inlist) ? PHP_EOL . "  <li>" : '';
        $txt = $this->ProcessLine($ln);
//        $this->text .= ($this->inlist) ? "</li>" : '';
        if ($txt) {
          $s = ($this->inlist && $txt !== '</ul>') ? PHP_EOL . "  <li>" : '';
          $e = ($this->inlist && $txt !== '</ul>') ? "</li>" : '';
          $this->text .= $s . $txt . $e;
        }
      }
    }
    $this->CheckOpenFlags();
    return $this->text;
/*
    if (\strpos($ret, self::MARKER_START)) {
//    $ret = nl2br(stripslashes($value));
    }
*/
  }
}

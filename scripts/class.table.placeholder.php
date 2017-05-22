<?php
namespace dana\table;

//use dana\core;

require_once 'class.basetable.php';

/**
  * place holder tag table - part of the process of creating bespoke emails
  * @version dana framework v.3
*/

class placeholder extends tagtable {
  const PLACEHOLDER_START = '[%';
  const PLACEHOLDER_END = '%]';

  static public $instance;
  public $account = false;
  public $contact;
  public $tag;
  public $customfields = [];

  function __construct() {
    parent::__construct('placeholder', 'tag');
  }

  static function StartInstance($id = 0) {
    require_once 'class.table.account.php';
    if (!isset(self::$instance)) {
      self::$instance = new \dana\table\placeholder($id);
    }
    return self::$instance;
  }

  protected function AssignFields() {
    parent::AssignFields();
    $this->tag = $this->AddField('tag', self::DT_STRING);
    $this->AddField('tablename', self::DT_STRING);
    $this->AddField('fieldname', self::DT_STRING);
  }

  private function CheckAccountExists() {
    $ret = (account::$instance);
    if ($ret) {
      $this->account = account::$instance;
      $this->contact = $this->account->Contact();
    }
    return $ret;
  }

  private function LookupTag($tag, $default = false) {
    if ($this->FindByTag($tag)) {
      $table = $this->GetFieldValue('tablename', false, false);
      $field = $this->GetFieldValue('fieldname', false, false);
      switch ($table) {
        case 'account':
          $ret = ($this->CheckAccountExists())
            ? $this->account->GetFieldValue($field, '(none)') : '(no account)';
          break;
        case 'contact':
          $ret = ($this->CheckAccountExists())
            ? $this->contact->GetFieldValue($field, '(none)') : '(no contact)';
          break;
        default :
          $ret = 'TAG=' . $tag;
          break;
      }
    } else {
      $ret = (isset($this->customfields[$tag]))
        ? $this->customfields[$tag]
        : (($default) ? $default : "[{$tag}]");
    }
    return $ret;
  }

  private function BasicTagLookup($tag) {
//    $this->account = account::StartInstance();
//    $this->contact = $this->account->Contact();
    switch($tag) {
      case 'contactfullname':
        $ret = ($this->CheckAccountExists())
          ? $this->contact->FullContactName() : '(no contact)';
        break;
      case 'minisite':
        if ($this->CheckAccountExists()) {
          $cm = GetCoreMode($modetype = \dana\table\coremode::MODETYPE_LIVE);
          $ret = \IncludeTrailingPathDelimiter($cm->domainurl) . $this->account->GetFieldValue('nickname');
        } else {
          $ret = '(no account)';
        }
        break;
      case 'year':
        $ret = date('Y');
        break;
      case 'today':
        $ret = date('D, j M Y');
        break;
      case 'now':
        $ret = date('Y-m-d H:i:s');
        break;
      default:
        $ret = false;
    }
    return $ret;
  }

  public function FormatLine($text, $defaultcustomtagvalue = false) {
//    $this->account = account::StartInstance();
//    $this->contact = $this->account->Contact();
    $offset = strlen(self::PLACEHOLDER_START);
    do {
      $posstart = strpos($text, self::PLACEHOLDER_START);
      if ($posstart !== false) {
        $posend = strpos($text, self::PLACEHOLDER_END, $posstart);
        if ($posend !== false) {
          $len = $posend - $posstart - $offset;
          $tag = substr($text, $posstart + $offset, $len);
          $word = $this->BasicTagLookup($tag);
          if (!$word) {
            $word = $this->LookupTag($tag, $defaultcustomtagvalue);
          }
          $text = substr($text, 0, $posstart) . $word . substr($text, $posend + $offset);
        }
      }
    } while ($posstart !== false);
    return $text;
  }

  public function Show() {
    return ($this->exists)
      ? $this->GetFieldValue('tag')
      : '';
  }

}

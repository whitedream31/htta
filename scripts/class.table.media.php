<?php
namespace dana\table;

require_once 'class.basetable.php';

/**
  * media table - container and manager for images (logo and galleries)
  * @version dana framework v.3
*/

class media extends idtable {

  function __construct($id = 0) {
    parent::__construct('media', $id);
  }

  protected function AssignFields() {
    parent::AssignFields();
    $this->AddField('galleritemid', self::DT_FK);
    $this->AddField('imgtype', self::DT_STRING);
    $this->AddField('imgsize', self::DT_INTEGER);
    $this->AddField('height', self::DT_INTEGER);
    $this->AddField('imgname', self::DT_STRING);
    $this->AddField('thumbnail', self::DT_STRING);
    $this->AddField('originalname', self::DT_STRING);
  }
/*
  static public function GetHighestImageValue($galleryid) {
    $query =
      'SELECT MAX(mi.`height`) AS maxheight FROM `gallery` g ' .
      'INNER JOIN `galleryitem` gi ON g.`id` = gi.`galleryid` ' .
      'INNER JOIN `media` mi ON gi.`largemediaid` = mi.`id` ' .
      'WHERE g.`id` = ' . (int) $galleryid;
    $result = \dana\core\database::Query($query);
    $line = $result->fetch_assoc();
    if ($line) {
      $ret = ((int) $line['maxheight']);
    } else {
      $ret = -1;
    }
    $result->close();
    return $ret;
  }

  public function UpdateProperties($src) {
    require_once 'class.formbuilderfilewebimage.php';
    if ($src instanceof \dana\formbuilder\formbuilderfilewebimage) {
      $this->SetFieldValue('imgtype', $src->file['type']);
      $this->SetFieldValue('originalname', $src->file['name']);
      $this->SetFieldValue('imgsize', $src->file['size']);
      $this->SetFieldValue('imgname', $src->newimgfilename);
      $this->SetFieldValue('thumbnail', $src->newimgthumbnail);
      //$this->SetFieldValue('srcwidth', $src->srcwidth);
      $this->SetFieldValue('height', $src->srcheight);
    }
  }

  public function AssignFromWebImage($src) {
    if (!$src->usecurrentfile) {
      $this->UpdateProperties($src);
    }
  }

  static public function FindNextImgID($accid) {
    $query = 'SELECT MAX(`imgid`) as maximgid FROM `media` WHERE `accountid` = ' . $accid;
    $result = \dana\core\database::Query($query);
    $line = $result->fetch_assoc();
    if ($line) {
      $id = ((int) $line['maximgid']) + 1;
    } else {
      $id = 1;
    }
    $result->free();
    return $id;
  }
*/
}

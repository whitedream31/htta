<?php
namespace dana\table;

require_once 'class.basetable.php';
//require_once('class.table.gallerycomment.php');

/**
  * gallery item table
  * @version dana framework v.3
*/

class galleryitem extends idtable {

  function __construct($id = 0) {
    parent::__construct('galleryitem', $id);
  }

  protected function AssignFields() {
    parent::AssignFields();
    $this->AddField('galleryid', self::DT_FK);
    $this->AddField('title', self::DT_STRING);
    $this->AddField('description', self::DT_DESCRIPTION);
    $this->AddField('mediaid', self::DT_FK);
    $this->AddField(\dana\table\basetable::FN_STATUS, self::DT_STATUS);
  }

  protected function AfterPopulateFields() {
  }

  public function Show($path = '') {
    $mediaid = $this->GetFieldValue('mediaid');
    $media = new \dana\table\media($mediaid);
    $title = $this->GetFieldValue('title');
    $thumbsrc = $path . $media->GetFieldValue('thumbnail');
    $largesrc = $path . $media->GetFieldValue('imgname');
    $img = "<img src='{$thumbsrc}' alt='' />";
    $link = "<a href='{$largesrc}' title='{$title}' data-gallery>{$img}</a>";
    return $link;
  }
}

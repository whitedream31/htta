<?php
namespace dana\process;

require_once 'class.table.newsletter.php';

/**
  * process requests
  * @version dana framework v.3
*/

// functions

function DoDownloadFile($file, $filename = false) {
  if (!$filename) {
    $filename = $file;
  }
  if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
  }
}

function DoUpdateDownloadCounter($nl) {
  $nl->UpdateCounter();
}

function ProcessNewsletterDownload() {
  $id = GetGet('id');
  $nl = new \dana\table\newsletter($id);
  if ($nl->exists) {
    $link = '..' . DIRECTORY_SEPARATOR . $nl->GetFieldValue('url');
    DoDownloadFile($link);
    DoUpdateDownloadCounter($nl);
  }
}

function ProcessCalendarDownload() {
  $id = GetGet('id');
  $event = new \dana\table\event($id);
  if ($event->exists) {
    $file = $event->GenerateICS();
//    DoDownloadFile($file, $event->GetFieldValue('title') . '.ics');
  }
}

// main

$ty = GetGet('ty');

switch ($ty) {
  case 'nl':
    ProcessNewsletterDownload();
    break;
  case 'ics':
    ProcessCalendarDownload();
    break;
}

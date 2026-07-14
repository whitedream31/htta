<?php

require_once '../Database/Database.php';

$action = $_GET['action'] ?? '';

function slug($text)
{
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

if ($action == "list") {

  $sql="SELECT e.*, v.description AS venue FROM event e LEFT JOIN venue v ON v.id=e.venueid WHERE e.status='A' ORDER BY e.startdate";

  $result = \HTTA\Database\Database::query($sql);

  $upcoming = [];
  $past = [];

  $today = date("Y-m-d");

  while($row = $result->fetch_assoc()) {
    if($row['startdate'] < $today)
      $past[] = $row;
    else
      $upcoming[] = $row;
  }

  echo json_encode([
    "upcoming" => $upcoming,
    "past" => $past
  ]);
  exit;
}

if ($action == "venues") {

    $sql = "SELECT id, description FROM venue WHERE status='A' ORDER BY ref";

    $result = \HTTA\Database\Database::query($sql);

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    echo json_encode($rows);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
  echo json_encode(["ok" => false, "error" => "No data"]);
  exit;
}

if ($action == "save") {

    if (empty($data['id'])) {

        $year = date("Y", strtotime($data['startdate']));
        $ref = $year . "-" . slug($data['description']);

        $stmt = \HTTA\Database\Database::prepare("
        INSERT INTO event
        (ref, description, venueid, content, startdate, enddate, starttime, bookingurl, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'A')
        ");

        $stmt->bind_param(
            "ssisssss",
            $ref,
            $data['description'],
            $data['venueid'],
            $data['content'],
            $data['startdate'],
            $data['enddate'],
            $data['starttime'],
            $data['bookingurl']
        );

        $stmt->execute();
    } else {

        $stmt = \HTTA\Database\Database::prepare("
        UPDATE event SET
            description=?,
            venueid=?,
            content=?,
            startdate=?,
            enddate=?,
            starttime=?,
            bookingurl=?
        WHERE id=?
        ");

        $stmt->bind_param(
            "sisssssi",
            $data['description'],
            $data['venueid'],
            $data['content'],
            $data['startdate'],
            $data['enddate'],
            $data['starttime'],
            $data['bookingurl'],
            $data['id']
        );

        $stmt->execute();
    }

    echo json_encode(["ok" => true]);
    exit;
}

if ($action == "copy") {
    $stmt = \HTTA\Database\Database::prepare("
        SELECT description, venueid, content, startdate, enddate, starttime, bookingurl
        FROM event
        WHERE id=?
    ");

    $stmt->bind_param("i", $data['id']);
    $stmt->execute();

    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    if (!$event) {
        echo json_encode(["ok"=>false]);
        exit;
    }

    // move dates forward 1 year
    $startdate = date("Y-m-d", strtotime($event['startdate']." +1 year"));
    $enddate   = date("Y-m-d", strtotime($event['enddate']." +1 year"));

    // build new ref
    $year = date("Y", strtotime($startdate));
    $ref = $year . "-" . slug($event['description']);

    $insert = \HTTA\Database\Database::prepare("
        INSERT INTO event
        (ref, description, venueid, content, startdate, enddate, starttime, bookingurl, status)
        VALUES (?,?,?,?,?,?,?,?, 'A')
    ");

    $insert->bind_param(
        "ssisssss",
        $ref,
        $event['description'],
        $event['venueid'],
        $event['content'],
        $startdate,
        $enddate,
        $event['starttime'],
        $event['bookingurl']
    );

    $insert->execute();

    echo json_encode([
        "ok"=>true,
        "new_start" => $startdate
    ]);

}

if ($action == "delete") {

    $stmt = \HTTA\Database\Database::prepare("UPDATE event SET status='D' WHERE id=?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();

    echo json_encode(["ok" => true]);
}

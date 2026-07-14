<?php
namespace HTTA\Control;

require_once '../Database/Database.php';

/*
    <article class="card float-start col-sm-10 col-md-6 m-1">
      <section class="date">
        <time datetime="2024-03-16">
          <span>16</span>
          <span>Mar</span>
        </time>
      </section>
      <section class="card-cont">
      <!--<section class="card-cont">-->
        <!--<small>Kedington Community Centre</small>-->
        <h3>Quiz Night</h3>
        <div class="even-date">
         <p>
           <i class="fa-solid fa-calendar"></i><span>Saturday 16th March 2024</span>
         </p>
         <p>
           <i class="fa-solid fa-clock"></i><span>Doors: 7pm - Start: 7:30pm</span>
         </p>
        </div>
        <div class="even-info">
          <p>
            <i class="fa-solid fa-map"></i>
            Kedington Community Centre,
            <span>Great Meadow, Arms Ln, Kedington, Haverhill CB9 7QQ</span>
          </p>
          <p><strong>&pound;10</strong> for members, <strong>&pound;12</strong> for non-members</p>
          <p>Food included</p>
          <a href="https://forms.gle/oM5Q2LptS6u8Rqkc6">Book</a>
        </div>
      </section>
    </article>
 */

use HTTA\Database\Database;
use HTTA\Database\DatabaseException;

class BuilderEvents {

  private function dataDetails(array $line): array {
    return [
      'id' => $line['id'],
      'ref' => $line['ref'],
      'description' => $line['description'],
      'startDate' => $line['startdate'],
      'endDate' => $line['enddate'],
      'startTime' => $line['starttime'],
      'details' => $line['content'],
      'venueName' => $line['venuename'],
      'venueAddress' => $line['venueaddress'],
      'bookingURL' => $line['bookingurl']
    ];
  }

  /**
   * find all recent events
   * @throws DatabaseException
   */
  public function findRecentEvents(): array {
    $sql = "SELECT e.*, v.description as venuename, v.address as venueaddress FROM `event` e " .
      "INNER JOIN `venue` v ON e.`venueid` = v.`id` " .
      "WHERE e.`startdate` < NOW() " .
      "ORDER BY e.`startdate` ASC";
    $list = [];
    $result = Database::query($sql);;
    while ($line = $result->fetch_assoc()) {
      $list[] = $this->dataDetails($line);
    }
    $result->free();
    return $list;
  }

  /**
   * find all future events
   * @throws DatabaseException
   */
  public function findFutureEvents(): array {
    $sql = "SELECT e.*, v.description as venuename, v.address as venueaddress FROM `event` e " .
      "INNER JOIN `venue` v ON e.`venueid` = v.`id` " .
      "WHERE e.`startdate` >= NOW() " .
      "ORDER BY e.`startdate` ASC";
    $list = [];
    $result = Database::query($sql);;
    while ($line = $result->fetch_assoc()) {
      $list[] = $this->dataDetails($line);
    }
    $result->free();
    return $list;
  }

  static function getDetails(): array {
    $events = new BuilderEvents();
    return [
      'data' => [
        'recent' => $events->findRecentEvents(),
        'future' => $events->findFutureEvents()
      ],
      'status' => 'ok'
    ];
  }
}

header("Content-type:application/json");
echo json_encode(BuilderEvents::getDetails());

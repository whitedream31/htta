<?php

namespace HTTA\Database;

require_once __DIR__ . '/TableID.php';

/**
 * table EMAIL MESSAGE class for HTTA
 *
 * @author ian.stewart
 */
class EmailMessageTable extends TableID {
  const STATUS_PENDING = 'P';
  const STATUS_SENT = 'S';
  const STATUS_ERROR = 'E';

  //private ?\mlsb\database\tableprocessor\Task $processor = null;

  function __construct(int $id = 0) {
    parent::__construct('emailmessage', $id);
  }

  protected function assignFields(): void {
    $this->assignStandardFields([self::INC_STATUS]);
    parent::assignFields();
    $this->addField('name', self::DT_STRING);
    $this->addField('emailaddress', self::DT_STRING);
    $this->addField('message', self::DT_TEXT);
    $this->addField('createstamp', self::DT_DATETIME);
    $this->addField('sentstamp', self::DT_DATETIME);
  }
}

<?php

namespace HTTA\Database;

use HTTA\Database\TableID;

/**
 * base class for lookup tables
 * @abstract
 */
abstract class TableLoopUp extends TableID {
  protected string $ref;
  protected string $description;

  function __construct(string $tableName, int $id = 0) {
    if (is_numeric($id)) {
      parent::__construct($tableName, $id);
    } elseif (is_string($id)) {
      parent::__construct($tableName);
      $this->findByRef($id);
    }
  }

  protected function afterPopulateFields(): void {
    $this->ref = $this->getFieldValue(self::FN_REF);
    $this->description = $this->getFieldValue(self::FN_DESCRIPTION);
  }

  protected function assignFields(): void {
    $this->assignStandardFields([
      self::INC_REF, self::INC_DESC, self::INC_STATUS
    ]);
    parent::assignFields();
  }

  protected function keyWithValue(?string $newValue = null): string {
    return '`' . self::FN_ID . '` = ' . (($newValue) ? $newValue : $this->ID());
  }
}

<?php

namespace HTTA\Database;

use HTTA\Database\TableBase;

/**
 * base class for tables with foreign key as primary key
 * primary key must be defined in constructor:
 * $keyname is the column name and $keytype is the column type (default: DT_FK)
 * @abstract
 */
abstract class TableForeign extends TableBase {
  protected string $keyName;
  protected string $keyType;

  function __construct(string $tableName, int $id, string $keyName, $keyType = self::DT_FK) {
    $this->keyName = $keyName;
    $this->keyType = $keyType;
    parent::__construct($tableName);
    $this->keyValue = $id;
    $this->findByKey($id);
  }

  protected function assignFields(): void {
    $this->addField($this->keyName, $this->keyType);
  }

  public function findByKey($value): bool {
    $ret = $this->findByField($this->keyName, $value);
    if (!$ret) {
      $this->setFieldValue($this->keyName, $value);
    }
    return $ret;
  }

  public function getKey(): int {
    return $this->keyValue;
  }

  public function refresh(): void {}

  protected function keyWithValue(?string $newValue = null): string {
    $id = (int) $this->getFieldValue($this->keyName);
    return "`{$this->keyName}` = " . (($newValue) ? $newValue : $id);
  }

  protected function updateKey(): string {
    return '';
  }

}

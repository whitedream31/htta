<?php

namespace HTTA\Database;

use HTTA\Database\TableBase;

/**
 * base class for tables with two fields for primary key
 * @abstract
 */
abstract class TableLink extends TableBase {

  public string $id1name;
  public string $id2name;

  public int $id1;
  public int $id2;

  /**
   * @throws DatabaseException
   */
  function __construct(string $tableName, string $id1name, string $id2name, int $id1value = 0, int $id2value = 0) {
    list($this->id1, $this->id2) = [$id1value, $id2value];
    list($this->id1name, $this->id2name) = [$id1name, $id2name];
    parent::__construct($tableName);
    $this->findByKey($id1value, $id2value);
  }

  protected function assignFields(): void {
    $this->id1 = (int) $this->addField($this->id1name, self::DT_ID);
    $this->id2 = (int) $this->addField($this->id2name, self::DT_ID);
  }

  public function refresh(): void {}

  /**
   * @throws DatabaseException
   */
  public function findByKey(int $value1, int $value2): bool {
    $sql = "SELECT * FROM `{$this->tableName}` " .
      "WHERE `{$this->id1name}` = \"{$value1}\" AND `{$this->id2name}` = \"{$value2}\"";
    $result = \HTTA\Database\Database::query($sql);
    $line = $result->fetch_assoc();
    $result->free();
    $this->exists = !empty($line);
    if ($this->exists) {
      $this->populateFields($line);
    } else {
      $this->setFieldValue($this->id1name, $value1);
      $this->setFieldValue($this->id2name, $value2);
    }
    return $this->exists;
  }

  protected function keyWithValue($newValue = null): string {
    return '`' . $this->id1name . '` = ' . (int) $this->getFieldValue($this->id1name) . ' AND ' .
      '`' . $this->id2name . '` = ' . (int) $this->getFieldValue($this->id2name);
  }

  protected function updateKey(): string {
    return '';
  }
}
<?php

namespace HTTA\Database;

use HTTA\Database\TableBase;

require_once __DIR__ . '/TableBase.php';

/**
 * base class for tables with id as primary key
 * @abstract
 */
abstract class TableID extends TableBase {

  function __construct(string $tableName, int|string $id = 0) {
    $this->keyValue = $id;
    parent::__construct($tableName);
    if (is_int($id)) {
      $this->findByKey($id);
    }
  }

  protected function assignFields(): void {
    $this->addField(self::FN_ID, self::DT_ID);
  }

  public function findByKey(int $value): bool {
    return $this->findByField(self::FN_ID, $value);
  }

  public function findByRef(string $value, bool $exists = true): bool {
    return $this->findByField(self::FN_REF, $value, $exists);
  }

  public function ref(): string {
    return $this->getFieldValue(self::FN_REF);
  }

  public function ID(): int {
    return (int) $this->getFieldValue(self::FN_ID);
  }

  public function refresh(): void {
    if ($this->exists) {
      $id = $this->ID();
      $this->findByKey($id);
    }
  }

  private function moveItemInOrder(string $itemId, int $order, string $orderFieldName = 'itemorder'): void {
    $query =
      "UPDATE `{$this->tableName}` SET `{$orderFieldName}` = " . $order .
      ' WHERE ' . $this->keyWithValue($itemId);
    \HTTA\Database\Database::query($query);
  }

  protected function keyWithValue(?string $newValue = null): string {
    return '`' . self::FN_ID . '` = ' . (($newValue) ? $newValue : $this->ID());
  }

  protected function updateKey(): int {
    $ret = \HTTA\Database\Database::lastInsertID();
    $this->setFieldValue(self::FN_ID, $ret);
    return $ret;
  }

  public function getFieldsAsArray(): array {
    $ret = [];
    foreach ($this->fieldList as $field) {
      $name = $field[self::FA_NAME];
      $ret[$name] = $this->getFieldValue($name);
    }
    return $ret;
  }
}
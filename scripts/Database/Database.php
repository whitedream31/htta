<?php

namespace HTTA\Database;

//use dana\database\table\ID;
//use dana\core\lib\Utils;

/** database library for MyLocalSmallBusiness (dana project)
 * @author written by Ian Stewart (c) 2012 Whitedream Software
*/

require_once '../Database/Consts.php';
require_once '../Database/UserSettings.php';
require_once '../Lib/Utils.php';

class DatabaseException extends \Exception{};

class Database {

  static public Database $instance;

  private \mysqli $connection;

  /**
   * @throws DatabaseException
   */
  function __construct() {
    $this->openConnection();
  }

  static public function getInstance(): Database {
    if (!isset(self::$instance)) {
      self::$instance = new database();
    }
    return self::$instance;
  }

  private function openConnection(): void {
    $this->connection = new \mysqli(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if ($this->connection->connect_errno) {
      throw new DatabaseException(
        'Failed to connect to MySQL: (' . $this->connection->connect_errno . ') ' . $this->connection->connect_error);
    }
  }

  static public function lastInsertID(): int {
    return self::$instance->connection->insert_id;
  }

  static public function lastErrorMessage(): string {
    return self::$instance->connection->error;
  }

  /**
   * retrieve a list of ids based on the specified table with optional status and order value
   * @param string $tableName name of the table to search
   * @param bool|mixed $where string or false (eg. `ref` = 'abc')
   * @param mixed|string $order string or false (eg. sortorder)
   * @param mixed $status string or false (eg. \dana\database\table\Base::STATUS_ACTIVE)
   * @return array of ids
   * @throws DatabaseException
   */
  static public function getRows(
    string $tableName, ?string $where = null,
    ?string $order = \dana\database\table\Base::FN_REF,
    ?string $status = \dana\database\table\Base::STATUS_ACTIVE,
    ?string $returnField = \dana\database\table\Base::FN_ID,
    bool $returnInt = true
  ): array {
    $statusClause = ($status) ? ((($where) ? ' AND ' : '') . "`status` = '{$status}'") : '';
    $query = "SELECT `{$returnField}` FROM `{$tableName}` ";
    if ($where || $statusClause) {
      $query .= "WHERE {$where}{$statusClause}";
    }
    $query .= ($order) ? " ORDER BY {$order}" : '';
    $list = [];
    $result = self::query($query);
    while ($line = $result->fetch_assoc()) {
      $list[] = ($returnInt) ? (int) $line[$returnField] : $line[$returnField];
    }
    $result->free();
    return $list;
  }

  static public function query(string $sql): \mysqli_result|bool {
    $result = self::getInstance()->connection->query($sql);
    if (!$result) {
      throw new DatabaseException('Database query failed: ' . self::$instance->connection->error . "\nSQL: $sql");
    }
    return $result;
  }

  /**
   * @throws DatabaseException
   */
  static public function lookupDescriptionFromID(string $table, int $idValue, string $defaultValue = ''): string {
    $idName = \dana\database\table\Base::FN_ID;
    $descName = \dana\database\table\Base::FN_DESCRIPTION;
    $query = "SELECT `{$descName}` FROM `{$table}` WHERE `{$idName}` = " . $idValue;
    $result = self::query($query);
    $line = $result->fetch_assoc();
    $result->free();
    return ($line) ? $line[$descName] : $defaultValue;
  }

  /**
   * @throws DatabaseException
   */
  static public function lookupRefFromID(string $table, int $idValue, string $defaultValue = ''): string {
    $idName = \dana\database\table\Base::FN_ID;
    $refName = \dana\database\table\Base::FN_REF;
    $query = "SELECT `{$refName}` FROM `{$table}` WHERE `{$idName}` = " . (int) $idValue;
    $result = self::query($query);
    $line = $result->fetch_assoc();
    $result->free();
    return ($line) ? $line[$refName] : $defaultValue;
  }

  /**
   * @throws DatabaseException
   */
  static public function lookupListByQuery(string $query, string $keyField = \dana\database\table\Base::FN_ID): array {
    $list = [];
    $result = self::query($query);
    while ($line = $result->fetch_assoc()) {
      $list[] = $line[$keyField];
    }
    $result->free();
    return $list;
  }

  static public function countRows(string $table, ?string $where = '', ?string $status = \dana\database\table\Base::STATUS_ACTIVE): int {
    $query = "SELECT COUNT(*) AS cnt FROM `{$table}`";
    $statusClause = ($status) ? ((($where) ? ' AND ' : '') . "`status` = '{$status}'") : '';
    if ($where || $statusClause) {
      $query .= ' WHERE ' . $where . $statusClause;
    }
    $result = self::query($query);
    $line = $result->fetch_assoc();
    $result->free();
    return (int) $line['cnt'];
  }

  static public function populateList(string $query, string $field = 'id'): array {
    $list = [];
    $result = self::query($query);
    while ($line = $result->fetch_assoc()) {
      $list[] = $line[$field];
    }
    $result->free();
    return $list;
  }

  static public function retrieveLookupList(
    string $tableName,
    string $descriptionField = \dana\database\table\Base::FN_DESCRIPTION,
    ?string $orderField = \dana\database\table\Base::FN_REF,
    string $keyField = \dana\database\table\Base::FN_ID,
    ?string $whereClause = "`status` = '" . \dana\database\table\Base::STATUS_ACTIVE . "'"
  ): array {
    $query = 'SELECT `' . $keyField . '`, `' . $descriptionField . '` FROM `' . $tableName . '`' .
      (($whereClause) ? '' : ' WHERE ' . $whereClause) .
      (($orderField) ? '' : ' ORDER BY ' . $orderField);
    return self::retrieveLookupListByQuery($query, $keyField, $descriptionField);
  }

  static public function retrieveLookupListByQuery(
    string $query,
    string $keyField = \dana\database\table\base::FN_ID,
    string $descriptionField = \dana\database\table\base::FN_DESCRIPTION): array {
    $list = [];
    $result = self::query($query);
    while ($line = $result->fetch_assoc()) {
      $id = $line[$keyField];
      $desc = $line[$descriptionField];
      $list[$id] = $desc;
    }
    $result->free();
    return $list;
  }

  static function selectFromTableByRef(string $tableName, string $ref, string $where = ''): array|bool {
    $query = "SELECT * FROM `{$tableName}` WHERE `ref` = '{$ref}'";
    if ($where != '') {
      $query .= ' AND ' . $where;
    }
    $result = self::query($query);
    $line = $result->fetch_assoc();
    $result->free();
    return $line;
  }

  /**
   * find a row based on a foreign field (ID) value
   * @param string $tableName
   * @param string $fieldName
   * @param string $fieldValue
   * @param string $getField
   * @return bool|int (false if not found, id if found(
   */
  static function selectIDFromTableByFK(string $tableName, string $fieldName, string $fieldValue, string $getField = table\Base::FN_ID): ?int {
    $ret = self::selectFromTableByField($tableName, $fieldName, (int) $fieldValue, $getField);
    return ($ret === null) ? null : (int) $ret;
  }

  static function selectFromTableByField(string $tableName, string $fieldName, string|int $fieldValue, string $getField = '*'): null|string|array {
    $ret = null;
//    if ($fieldValue) {
      if (!is_numeric($fieldValue)) {
        $fieldValue = '"' . $fieldValue . '"';
      }
      $query = "SELECT {$getField} FROM `{$tableName}` WHERE `{$fieldName}` = {$fieldValue}";
      $result = self::query($query);
      if ($result->num_rows > 0) {
        $line = $result->fetch_assoc();
        $ret = ($getField != '*')
          ? $line[$getField] : $line;
      }
      $result->free();
//    }
    return $ret;
  }

  static public function selectDescriptionFromLookup(string $tableName, int|string $id): array|string {
    return self::selectFromTableByField(
      $tableName, \dana\database\table\base::FN_ID, (string) $id, \dana\database\table\base::FN_DESCRIPTION
    );
  }

  static public function getAffectedRows(): int {
    return self::$instance->connection->affected_rows;
  }

  static function transactionBegin(): void {
    self::getInstance()->connection->autocommit(false);
  }

  static function transactionCommit(): void {
    self::getInstance()->connection->commit();
    self::$instance->connection->autocommit(true);
  }

  static function transactionRollback(): void {
    self::getInstance()->connection->rollback();
  }

  static function prepare($sql) : \mysqli_stmt|false {
    return self::getInstance()->connection->prepare($sql);
  }

  /*
  static function findIDFromTableByRef(string $tableName, string $ref, bool|string $where = false): ?int {
    $query = "SELECT `id` FROM `{$tableName}` WHERE `ref` = '{$ref}'";
    if ($where) {
      $query .= ' AND ' . $where;
    }
    $result = self::query($query);
    $line = $result->fetch_assoc();
    $result->free();
    return ($line) ? (int) $line['id'] : null;
  }
*/
}

// start the database as a singleton
database::getInstance();

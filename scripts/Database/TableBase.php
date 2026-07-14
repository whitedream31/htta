<?php

namespace HTTA\Database;

/**
  * base table classes for all tables for HTTA
  * modified: 15 sep 2022
  * @version  dana5
*/

require_once '../Lib/Utils.php';
require_once __DIR__ . '/Database.php';

/**
  * base class for all table related classes
  * @abstract
*/
abstract class TableBase {
  const STATUS_ACTIVE = 'A';
  const STATUS_DELETED = 'D';
  const STATUS_NEW = 'N';
  const STATUS_PENDING = 'P';

  const INC_REF = self::FN_REF;
  const INC_DESC = self::FN_DESCRIPTION;
  const INC_STATUS = self::FN_STATUS;
  const INC_APPLICATION_ID = self::FN_APPLICATION_ID;
  const INC_LANGUAGE_ID = self::FN_LANGUAGE_ID;
  const INC_ADDRESS_ID = self::FN_ADDRESS_ID;
  const INC_USER_ID = self::FN_USERID;

  const STORE_RESULT_INSERT = -2;
  const STORE_RESULT_ERROR = -1;
  const STORE_RESULT_NO_CHANGE = 0;
  const STORE_RESULT_MODIFIED = 1;

  const FA_VALUE = 'value';
  const FA_NAME = 'name';
  const FA_DATATYPE = 'dt';
  const FA_MODIFIED = 'md';
  const FA_DEFAULT = 'default';

  const FN_ID = 'id';
  const FN_REF = 'ref';
  const FN_DESCRIPTION = 'description';
  const FN_STATUS = 'status';
  const FN_USERID = 'userid';
  const FN_LANGUAGE_ID = 'languageid';
  const FN_ADDRESS_ID = 'addressid';
  const FN_APPLICATION_ID = 'applicationid';

  const DT_STRING = 's';
  const DT_TEXT = 't';
  const DT_INTEGER = 'i';
  const DT_FLOAT = 'f';
  const DT_DATE = 'd';
  const DT_DATETIME = 'dt';
  const DT_BOOLEAN = 'b';
  const DT_ID = 'id';
  const DT_FK = 'fk';
  const DT_REF = 'ref';
  const DT_STATUS = 'st';
  const DT_DESCRIPTION = 'desc';

  public string $tableName;
  public bool $exists;
  public int|string $keyValue;
  public array $fieldList = [];
  public array $customFields = [];
  public int $lastInsertId = 0;
  public array $lastError = [];

  /**
   * @param string $tableName
   */
  function __construct(string $tableName) {
    $this->tableName = $tableName;
    $this->customFields = [];
    $this->fieldList = [];
    $this->exists = false;
    $this->assignFields();
  }

  abstract protected function assignFields(): void;
  abstract protected function keyWithValue(?string $newValue = null): string;
  abstract protected function updateKey(): mixed;
  abstract public function refresh(): void;

  protected function assignStandardFields(array $fieldList): void {
    foreach($fieldList as $field) {
      switch($field) {
        case self::INC_REF:
          $this->addField(self::FN_REF, self::DT_REF);
          break;
        case self::INC_DESC:
          $this->addField(self::FN_DESCRIPTION, self::DT_DESCRIPTION);
          break;
        case self::INC_STATUS:
          $this->addField(self::FN_STATUS, self::DT_STATUS);
          break;
        case self::INC_LANGUAGE_ID:
          $this->addField(self::FN_LANGUAGE_ID, self::DT_FK);
          break;
        case self::INC_ADDRESS_ID:
          $this->addField(self::FN_ADDRESS_ID, self::DT_FK);
          break;
        case self::INC_APPLICATION_ID:
          $this->addField(self::FN_APPLICATION_ID, self::DT_FK);
          break;
        case self::INC_USER_ID:
          $this->addField(self::FN_USERID, self::DT_FK);
          break;
      }
    }
  }

  public function status(): string {
    return $this->getFieldValue(self::FN_STATUS) ?? '';
  }

  public function statusIsActive(): bool {
    return $this->getFieldValue(self::FN_STATUS) === self::STATUS_ACTIVE;
  }

  /**
   * @param string $name
   * @return bool
   */
  public function fieldExists(string $name): bool {
    return isset($this->fieldList[$name]);
  }

  /**
   * assign a value to the specified field
   * @param string $name - the name of the field to change
   * @param string|int $value - the new value of the field
   * @param bool $forced - (optional) assign the value even if it appears to already have the value
   * @param bool $convertToEntities
   * @return int - 0 if not changed (value already assign and $forced is false), 1 assigned successfully, -1 if field name does not exist
   */
  public function setFieldValue(string $name, string|int $value, bool $forced = false, bool $convertToEntities = true): int {
    if ($this->fieldExists($name)) {
      if ($this->fieldList[$name][self::FA_VALUE] !== $value || $forced || !$this->exists) {
        $this->fieldList[$name][self::FA_MODIFIED] = true;
        switch ($this->fieldList[$name][self::FA_DATATYPE]) {
          case self::DT_STRING:
          case self::DT_TEXT:
            if ($convertToEntities) {
              $value = $this->convertToEntities($value);
            }
            break;
        }
        $this->fieldList[$name][self::FA_VALUE] = $value;
        $ret = 1; // modified value
      } else {
        $ret = 0; // no change required
      }
    } else {
      $ret = -1; // field does not exist
    }
    return $ret;
  }

  static public function convertToEntities(string $value): string {
    return ($value) ? \htmlentities($value, ENT_QUOTES) : ''; // self::SafeStringEscape($ret);
  }

  static public function convertFromEntities($value, $stripSlashes = false): bool|array|string {
    $ret = \html_entity_decode($value, ENT_QUOTES); // self::SafeStringEscape($ret);
    if ($stripSlashes) {
      $ret = \stripslashes($ret); // self::SafeStringEscape($ret);
    }
    return mb_convert_encoding($ret, 'UTF-8', 'UTF-8');
  }

  private function getTextUsingPlaceHolder($txt) {
    return $txt; // TOOD: reimplement
  }

  /**
   * @param string $name
   * @param string $default
   * @param bool $usePlaceHolders
   * @param bool $convertEntities
   * @return bool|string
   */
  public function getFieldValue(string $name, mixed $default = '', bool $usePlaceHolders = true, bool $convertEntities = true): mixed {
    $fld = ($this->fieldExists($name))
      ? $this->fieldList[$name]
      : null;
    if ($fld) {
      $ret = $fld[self::FA_VALUE];
      if ($ret === null) {
        $ret = $fld[self::FA_DEFAULT];
      } else {
        if (!$ret && $default) {
          $ret = $default;
        } else {
          switch ($fld[self::FA_DATATYPE]) {
            case self::DT_STRING:
            case self::DT_DESCRIPTION:
              if ($ret && $usePlaceHolders) {
                $ret = $this->getTextUsingPlaceHolder($ret);
              }
              if ($ret && $convertEntities) {
                $ret = $this->convertFromEntities($ret);
              }
              break;
            case self::DT_TEXT:
              if ($ret && $convertEntities) {
                $ret = $this->ConvertFromEntities($ret, true);
              }
              break;
          }
        }
      }
    } else {
      $ret = $this->getCustomFieldValue($name);
    }
    return $ret;
  }

  /**
   * @param string $name
   * @param string $value
   * @param bool $modify
   * @return string|null
   */
  public function assignFieldDefaultValue(string $name, string $value, bool $modify = false): ?string {
    if (isset($this->fieldList[$name])) {
      $this->fieldList[$name][self::FA_VALUE] = $value;
      $this->fieldList[$name][self::FA_DEFAULT] = $value;
      $this->fieldList[$name][self::FA_MODIFIED] = $modify;
      $ret = $value;
    } else {
      $ret = null;
    }
    return $ret;
  }

  /**
   * StringToPretty
   * Convert a string into a valid entity value. eg. 'This is a TEST' -> 'this-is-a-test'
   */
  static public function stringToPretty(string $value): string {
    return urlencode(str_replace(' ', '-', trim(strtolower($value))));
  }

  /**
   * do something before populating field list
   */
  protected function beforePopulateFields(): void {}

  /**
   * do something after populating field list
   */
  protected function afterPopulateFields(): void {}

  /** lookup the custom field value
   * @param string $fieldName
   * @return ?string (returns false if not found)
   */
  protected function getCustomFieldValue(string $fieldName): ?string {
    if (isset($this->customFields[$fieldName])) {
      $ret = $this->customFields[$fieldName];
      if (!$ret) {
        $this->customFields[$fieldName] = $this->assignCustomFieldValue($fieldName);
        $ret = $this->customFields[$fieldName];
      }
    } else {
      $ret = null;
    }
    return $ret;
  }

  /** find and assign the custom field value
   * @param string $fieldName
   * @return mixed - this is over-ridden by inherited classes
  */
  protected function assignCustomFieldValue(string $fieldName): mixed {
    return false; // to be overidden
  }

  protected function populateCustomFields(): void {
    foreach(array_keys($this->customFields) as $fieldName) {
      $this->customFields[$fieldName] = $this->assignCustomFieldValue($fieldName);
    }
  }

  protected function populateFields(?array $line): void {
    $this->beforePopulateFields();
    foreach($this->fieldList as $fld) {
      $name = $fld[self::FA_NAME];
      if (isset($this->fieldList[$name])) {
        $value = (isset($line[$name])) ? $line[$name] : false;
        $newValue = ($value === false)
          ? null
          : stripslashes(html_entity_decode($value, ENT_QUOTES));
        switch ($this->fieldList[$name][self::FA_DATATYPE]) {
          case self::DT_ID :
          case self::DT_FK :
          case self::DT_INTEGER :
          case self::DT_BOOLEAN :
            $newValue = (int) $newValue;
            break;
        }
        $this->fieldList[$name][self::FA_VALUE] = $newValue;
      }
    }
    $this->afterPopulateFields();
    $this->populateCustomFields();
  }

  /**
   * @param string $value
   * @return string
   */
  static function safeStringEscape(string $value): string {
    $len = strlen($value);
    $escapeCount = 0;
    $targetString = '';
    for($offset = 0; $offset < $len; $offset++) {
      switch($c = $value[$offset]) {
        case '"':
        case "'":
          // Escapes this quote only if its not preceded by an unescaped backslash
          if($escapeCount % 2 == 0) {
            $targetString .= "\\";
          }
          $escapeCount = 0;
          $targetString .= $c;
          break;
        case '\\':
          $escapeCount++;
          $targetString .= $c;
          break;
        default:
          $escapeCount = 0;
          $targetString .= $c;
      }
    }
    return $targetString;
  }

  /**
   * optional work to do BEFORE saving to database
   * @param int $fieldsToUpdate
   * @return void
   */
  protected function doPreSave(int $fieldsToUpdate): void {}

  /**
   * optional work to do AFTER saving to database
   * @param $saveResult
   * @return void
   */
  protected function doPostSave($saveResult): void {}

  protected function doBeforeStoreChanges() {}

  protected function assignStandardField($fieldName, $table): void {
    if ($this->fieldExists($fieldName)) {
      if ($table instanceof ID) {
        $this->setFieldValue($fieldName, $table->ID());
      }
    }
  }

  /**
   * cancel changed
   * @return int count of fields that have been cancelled as no longer modified
   */
  public function cancelChanges(): int {
    $cnt = 0;
    foreach ($this->fieldList as $fld) {
      $name = $fld[self::FA_NAME];
      if ($this->fieldIsModified($name)) {
        $this->fieldList[$name][self::FA_MODIFIED] = false;
        $cnt++;
      }
    }
    return $cnt;
  }

  /**
   * save changes to table
   * returns:
   * -1 = SQL Error
   * -2 - New Row Created
   *  0 - No Changes Made
   * +n - Update Made to Row with 'n' fields modified
   * @return int
   */
  public function storeChanges(): int {
    $this->doBeforeStoreChanges();
    $setList = [];
    $cnt = 0;
    foreach($this->fieldList as $fld) {
      $name = $fld[self::FA_NAME];
      if ($this->fieldIsModified($name)) {
        $value = $this->fieldList[$name][self::FA_VALUE];
        $dt = $this->fieldList[$name][self::FA_DATATYPE];
        switch($dt) {
          case self::DT_BOOLEAN:
            if (is_string($value)) {
              if ($value === 'false') {
                $value = false;
              } else {
                $value = (bool) $value;
              }
            }
            $value = $value ? 1 : 0;
            break;
          case self::DT_ID:
          case self::DT_FK:
          case self::DT_INTEGER:
            $value = (int) $value;
            break;
//          case self::DT_STRING:
//            $value = self::SafeStringEscape($value);
//            break;
          default:
            if ($value) {
              $escapedValue = addslashes($value);
              $value = "'{$escapedValue}'";
            } else {
              $value = 'NULL';
            }
        }
        $setList[$name] = $value;
        $this->fieldList[$name][self::FA_MODIFIED] = false;
        $cnt++;
      }
    }
    $this->doPreSave($cnt); // optional work to do before saving
    // if changes should be made ($cnt > 0) then
    //   if the record exists do an update, else do an insert
    if ($cnt) {
      if ($this->exists) {
        // do UPDATE
        $set = [];
        foreach ($setList as $setField => $setVal) {
          $set[] = "`{$setField}` = {$setVal}";
        }
        $setStr = implode(', ', $set);
        $query = "UPDATE `{$this->tableName}` SET {$setStr} WHERE " . $this->keyWithValue();
      } else {
        // do INSERT
        $this->lastInsertId = 0;
        $fieldList = [];
        $valueList = [];
        foreach ($setList as $setField => $setVal) {
          $valueList[] = $setVal; // str_replace('\'', '\\\'', $setval); --- from class.table.emailqueue -- remove it from there first
          $fieldList[] = "`{$setField}`";
        }
        $fieldListAsStr = implode(', ', $fieldList);
        $valueListAsStr = implode(', ', $valueList);
        $query = "INSERT INTO `{$this->tableName}` ({$fieldListAsStr}) VALUES ({$valueListAsStr})";
        $cnt = self::STORE_RESULT_INSERT; //-2;
      }
      try {
        \HTTA\Database\Database::query($query);
        if (!$this->exists) {
          $this->lastInsertId = (int) $this->updateKey();
          $this->exists = true; // exists now
        }
        $this->doPostSave($cnt); // optional work to do after saving
      } catch (\Exception $e) {
        $this->lastError = [
          'code' => $e->getCode(),
          'msg' => $e->getMessage()
        ];
        file_put_contents(
          'tableerror.txt',
          'Error: ' . date(DATE_RSS) . $this->lastError['code'] . ': ' . $this->lastError['msg'] . "\n\r",
          FILE_APPEND
        );
        $cnt = self::STORE_RESULT_ERROR; //-1;
      }
    }
    return $cnt;
  }

  /**
   * clear field list values and mark as row not exists
   * essentially creating a new row (without writing it to the table)
   * @return void
   */
  public function newRow(): void {
    foreach ($this->fieldList as $fld) {
      $name = $fld[self::FA_NAME];
      $this->fieldList[$name][self::FA_VALUE] = $this->fieldList[$name][self::FA_DEFAULT];
      $this->fieldList[$name][self::FA_MODIFIED] = false;
    }
    $this->exists = false;
  }

  /**
   * physically delete rows from related (foreign) tables and itself
   * to be overridden by each table class
  */
  public function deleteRows(): void {
    $this->doDeleteRow();
  }

  /*
   * delete actual row (by id)
  */
  protected function doDeleteRow(): void {
    $keys = $this->keyWithValue();
//    if (!TEST_MODE) {
      $sql = "DELETE FROM `{$this->tableName}` WHERE " . $keys;
      \HTTA\Database\Database::query($sql);
//    }
  }

  /*
   * called by DeleteRelatedRows to delete rows
  */
  protected function doDeleteRows(string $foreignTable, string $foreignKeyField, string|int $foreignKeyValue): void {
//    if (!TEST_MODE) {
      if (!is_numeric($foreignKeyValue)) {
        $foreignKeyValue = "'{$foreignKeyValue}'";
      }
      $sql = "DELETE FROM `{$foreignTable}` WHERE `{$foreignKeyField}` = {$foreignKeyValue}";
      \HTTA\Database\Database::query($sql);
//    }
  }

  /**
   * @param string $name
   * @param int|string $value
   * @return int|string
   */
  protected function parseValue(string $name, int|string $value): int|string {
    $ret = $value;
    if (isset($this->fieldList[$name])) {
      switch ($this->fieldList[$name][self::FA_DATATYPE]) {
        case self::DT_BOOLEAN :
          $ret = ($value = 'yes') ? 1 : 0;
          break;
      }
    }
    return $ret;
  }

  /**
   * return an initial value based on the 'standard' data types used in the dana framework
   * @param string $datatype
   * @return mixed
   */
  protected function getDefaultOnDataType(string $datatype): mixed {
    return match ($datatype) {
      self::DT_FK, self::DT_INTEGER, self::DT_FLOAT => 0,
      self::DT_DATETIME => date(\HTTA\Lib\Utils::DATE_STD),
      self::DT_BOOLEAN => false,
      self::DT_ID => -1,
      self::DT_STATUS => self::STATUS_ACTIVE,
      default => '',
    };
  }

  /**
    * @param string $name
    * @param string $datatype
    * @param mixed $default
    * @return mixed
   */
  protected function addField(string $name, string $datatype, mixed $default = null): mixed {
    $defValue = ($default) ? $default : $this->getDefaultOnDataType($datatype);
    $this->fieldList[$name] = [
      self::FA_NAME => $name,
      self::FA_VALUE => $defValue,
      self::FA_DATATYPE => $datatype,
      self::FA_DEFAULT => $defValue,
      self::FA_MODIFIED => false
    ];
    return $this->fieldList[$name];
  }

  /**
   * find a row based on the $fieldname and $value
   * if $exists is true it returns the state of $this exists and populates the fieldlist
   * if $exists is false it returns the $line array of columns from the database table
   * @param string $fieldName
   * @param string|int $value
   * @param bool $exists
   * @return bool
   */
  public function findByField(string $fieldName, string|int $value, bool $exists = true): bool {
    $line = \HTTA\Database\Database::selectFromTableByField(
      $this->tableName, $fieldName, $value
    );
    if ($exists) {
      $this->exists = ($line !== null);
      $ret = $this->exists;
      if ($this->exists) {
        $this->populateFields($line);
        $this->keyValue = (int) $this->getFieldValue($fieldName); //self::FN_ID);
      }
    } else {
      $ret = $line;
    }
    return $ret;
  }

  /**
   * @param string $fieldName
   * @return bool
   */
  public function fieldIsModified(string $fieldName): bool {
    return (bool) $this->fieldList[$fieldName][self::FA_MODIFIED];
  }

  public function updateStatus(string $value): int {
    $ret = self::STORE_RESULT_NO_CHANGE;
    if ($this->exists) {
      $flg = $this->setFieldValue(self::FN_STATUS, $value);
      if ($flg > 0) {
        $ret = $this->storeChanges();
      }
    }
    return $ret;
  }

  /*
   *  use to mark the status column as DELETED in the current table
  */
  public function markAsDeleted(bool $flg = true): int {
    $status = ($flg) ? self::STATUS_DELETED : '';
    return $this->updateStatus($status);
  }

  public function getRefValue(): string {
    return $this->getFieldValue(self::FN_REF);
  }

  public function getDescriptionValue(): string {
    return $this->GetFieldValue(self::FN_DESCRIPTION);
  }

  public function getStatusValue(): string {
    return $this->getFieldValue(self::FN_STATUS);
  }

  public function isDeleted(): bool {
    return $this->getFieldValue(self::FN_STATUS, false) === self::STATUS_DELETED;
  }

  public function isActive(): bool {
    return $this->getFieldValue(self::FN_STATUS, false) === self::STATUS_ACTIVE;
  }
}

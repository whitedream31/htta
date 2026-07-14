<?php

namespace HTTA\Lib;

//require_once './Consts.php';

/**
 * main static class library
 *
 * @author ians
 */
class Utils {

  const DF_SHORT_DATE = 'sd';
  const DF_SHORT_DATETIME = 'sdt';
  const DF_LONG_DATE = 'ld';
  const DF_LONG_DATETIME = 'ldt';
  const DF_MEDIUM_DATETIME = 'mdt';
  const DF_MEDIUM_DATE = 'md';
  const DF_DISPLAY_DATE = 'dd';
  const DF_STD_DATE = 'd';
  const DF_STD_DATETIME = 'dt';
  const DF_TIME_12 = 't12';
  const DF_TIME_24 = 't24';

  const DATE_STD = 'Y-m-d';
  const DATE_STD_WITH_TIME = 'Y-m-d H:i:s';

  /**
   * GetGet returns the $_GET element using filter_input for security
   *
   * @param string $name
   * @param mixed $default is returned if element not found
   * @param int $filter filter (eg. FILTER_SANITIZE_SPECIAL_CHARS
   * @param int|array $options options (eg. FILTER_REQUIRE_ARRAY)
   *
   * @return mixed
   */
  static public function getGet(string $name, ?string $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS, int|array $options = 0): mixed {
    $ret = filter_input(INPUT_GET, $name, $filter, $options);
    if ($ret === NULL) {
      $ret = $default;
    }
    return $ret;
  }

  /** GetPost returns the $_POST element using filter_input for security
   *
   * @param string $name
   * @param mixed $default is returned if element not found
   * @param int $filter
   * @param int|array $options (eg. FILTER_REQUIRE_ARRAY)
   * @return mixed
   */
  static public function getPost(string $name, ?string $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS, int|array $options = 0): mixed {
    $ret = filter_input(INPUT_POST, $name, $filter, $options);
    if ($ret === NULL) {
      $ret = $default;
    }
    return $ret;
  }

  /** returns the $_SERVER element using filter_input for security
   *
   * @param string $name
   * @param mixed $default is returned if element not found
   * @param int $filter options (eg. FILTER_REQUIRE_ARRAY)
   * @param int|array $options
   * @return mixed
   */
  static public function getServer(string $name, ?string $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS, int|array $options = 0): mixed {
    $ret = filter_input(INPUT_SERVER, $name, $filter, $options);
    if ($ret === NULL) {
      $ret = $default;
    }
    return $ret;
  }

/** GetCookie returns the $_COOKIE element using filter_input for security
  *
  * @param string $name
  * @param mixed $default is returned if element not found
  * @return mixed
 */
  static public function getCookie(string $name, ?string $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS, int|array $options = 0): mixed {
    $ret = filter_input(INPUT_COOKIE, $name, $filter, $options);
    if ($ret === NULL) {
      $ret = $default;
    }
    return $ret;
  }

//function array2csv($list, $delimiter = ',') {
//  $f = fopen('php://memory', 'r+');
//  foreach ($list as $item) {
//    fputcsv($f, $item, $delimiter, '', "\\");
//  }
//  rewind($f);
//  return stream_get_contents($f);
//}

// used by ArrayToString - not to be used directly
static public function doImplode(array $list, string $glue, string $enclosure): string {
  $ret = '';
  if (count($list) > 1) {
    foreach ($list as $ln) {
      if (strlen(trim($ln))) {
        $ret .= $enclosure . $ln . $enclosure . $glue;
      }
    }
  } else {
    $ret = $enclosure . reset($list) . $enclosure;
  }
  return $ret;
}

  /**
   * Convert array into a string
   *
   * @param array|string $value
   * @param string|null $glue
   * @param string $enclosure
   * @return string
   */
  static public function arrayToString(array|string $value, ?string $glue = null, string $enclosure = ''): string {
    if (is_null($glue)) {
      $glue = PHP_EOL;
    }
    if (is_array($value)) { // && count($value)) {
      $lst = [];
      foreach($value as $v) {
        if ((is_string($v) && trim($v) !== '') || (is_array($v)) && count($v)) {
          $lst[] = self::arrayToString($v, $glue);
        }
      }
      $ret = self::doImplode($lst, (is_null($glue)) ? '' : $glue, $enclosure);
    } else {
      $ret = $value;
    }
    return rtrim($ret, $glue);
  }

  /**
   * Convert a string into an array, separated by $separator (\n)
   *
   * @param string|array $value
   * @param string $separator
   *
   * @return array
   */
  static public function stringToArray(string|array $value, string $separator = "\n"): array {
    if (is_array($value)) {
      $ret = $value;
    } else {
//      $seplist = self::arrayToString($separator); // check $value is array, convert to string
      $ret = preg_split('/[\\' . $separator . ']+/', $value);// explode($separator, $value);
    }
    return $ret;
  }

  /**
   * returns a string containing a formatted date/time value
   * @param string $formatType DF_
   * @param string|int $value
   * @param string $defaultValue
   * @return string
  */
  static public function formatDateTime(string $formatType, int|string $value, string $defaultValue = ''): string {
    $ret = $defaultValue;
    if ($value && (!str_contains($value, '0000'))) {
      $time = (is_string($value)) ? strtotime($value) : $value;
      if ($time !== false) {
        switch ($formatType) {
          case self::DF_LONG_DATE:
            $ret = date('l, jS F Y', $time);
            break;
          case self::DF_LONG_DATETIME:
            $ret = date('D, jS F Y h:i a', $time);
            break;
          case self::DF_MEDIUM_DATETIME:
            $ret = date('jS F Y h:i a', $time);
            break;
          case self::DF_MEDIUM_DATE:
            $ret = date('jS F Y', $time);
            break;
          case self::DF_SHORT_DATE:
            $ret = date('d M Y', $time);
            break;
          case self::DF_SHORT_DATETIME:
            $ret = date('d M Y H:i', $time);
            break;
          case self::DF_TIME_12:
            $ret = date('g:i a', $time);
            break;
          case self::DF_TIME_24:
            $ret = date('H:i', $time);
            break;
          case self::DATE_STD:
          case self::DF_DISPLAY_DATE:
            $ret = date(self::DATE_STD, $time);
            break;
          case self::DATE_STD_WITH_TIME:
          case self::DF_STD_DATETIME:
            $ret = date(self::DATE_STD_WITH_TIME, $time);
            break;
        }
      }
    }
    return $ret;
  }

  static function isValidDate(string $date, string $format = 'Y-m-d'): bool {
    $dateObj = \DateTime::createFromFormat($format, $date);
    return $dateObj && $dateObj->format($format) == $date;
  }

  static function isValidTime(string $timeValue): bool {
    return (bool)(((preg_match("/^(1[0-2]|0?[1-9]):[0-5][0-9] (AM|PM)$/i", $timeValue))
      ? true
      : (preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $timeValue))));
  }

 /**
  * Get relative time from a time value to a pretty English textdomain
  *
  * @param string $startTime
  * @param bool|string $endTime (optional)
  * @return string
  */
 /*
  static public function relativeTime(string $startTime, bool|string $endTime = false): string {
    $ret = '';
    $start = (!ctype_digit($startTime)) ? strtotime($startTime) : $startTime;
    $finish = ($endTime === false) ? time() : $endTime;
    $diff = $finish - $start;
    if($diff == 0) {
      $ret = \dana\core\translation\Translator::lookupCommonRef(
        \dana\core\translation\Translator::TT_COMMON_NOW
      );
    } elseif($diff > 0) {
      $day_diff = floor($diff / 86400);
        if($diff < 60) {
          $ret = \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_JUST_NOW);
        } elseif($diff < 120) {
          $ret = \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_1MIN_AGO);
        } elseif($diff < 3600) {
          $ret = floor($diff / 60) . ' Utils.php' . \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_MINS_AGO);
        } elseif($diff < 7200) {
          $ret = \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_1HOUR_AGO);
        } elseif($diff < 86400) {
          $ret = floor($diff / 3600) . ' Utils.php' . \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_HOURS_AGO);
        } elseif($day_diff == 1) {
          $ret = \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_YESTERDAY);
        } elseif($day_diff < 7) {
          $ret = $day_diff . \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_DAYS_AGO);
        } elseif($day_diff < 31) {
          $ret = floor($day_diff / 7) . ' Utils.php' . \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_WEEKS_AGO);
        } elseif($day_diff < 90) {
          $ret = \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_LAST_MONTH);
        } else {
          $ret = floor($day_diff / 30) . ' Utils.php' . \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_MONTHS_AGO);
        }
    } else {
      $diff = abs($diff);
      $day_diff = floor($diff / 86400);
      if($day_diff == 0) {
        if($diff < 120) {
          $ret = \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_IN_A_MIN);
        } elseif($diff < 3600) {
          $ret = LibUtils . php\dana\core\translation\Translator::lookupDateCommonRef(\dana\core\translation\Translator::TT_COMMON_IN) .
            floor($diff / 60) . ' ' . \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_MINS);
        } elseif($diff < 7200) {
          $ret = \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_IN_AN_HOUR);
        } elseif($diff < 86400) {
          $ret = LibUtils . php\dana\core\translation\Translator::lookupCommonRef(\dana\core\translation\Translator::TT_COMMON_IN) .
            floor($diff / 3600) . ' ' . \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_HOURS);
        }
      } elseif($day_diff == 1) {
        $ret = \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_TOMORROW);
      } elseif($day_diff < 4) {
        $ret = date('l', $start);
      } elseif($day_diff < 7 + (7 - date('w'))) {
        $ret = \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_NEXT_WEEK);
      } elseif(ceil($day_diff / 7) < 4) {
        $ret = \dana\core\translation\Translator::lookupCommonRef(\dana\core\translation\Translator::TT_COMMON_IN) .
          ' Utils.php' . ceil($day_diff / 7) . ' ' . \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_WEEKS);
      } elseif((int) date('n', $start) === (int) date('n') + 1) {
        $ret = \dana\core\translation\Translator::lookupDateTimeRef(\dana\core\translation\Translator::TT_DATETIME_NEXT_MONTH);
      } else {
        $ret = date('F Y', $start);
      }
    }
    return $ret;
  }
*/
  /**
   * find and return the value from the array ($list) using the key
   * if not found use $default
   * @param array|null $list
   * @param string|int $key
   * @param mixed $default
   * @return mixed - return the value from the array using the key
   */
  static public function getItemFromArray(?array $list, string|int $key, ?string $default = null): mixed {
    return (isset($list[$key])) ? $list[$key] : $default;
  }

  static public function WriteDebugClassFile(string $contents): void {
    if (DEBUG_MODE) {
      file_put_contents('./_CLASSFILEFILE.txt', $contents, FILE_APPEND);
    }
  }

  /**
   * finds a named script that contains a named class, checks the file exists
   * loads the script and checks the class exists
   * filename is ".\{$filenameprefix}.{$classname}.php"
   * classname is "$namespace + $classname"
   * @param string $prefix the first part of the script filename eg. class.context (for class.context.classname.php)
   * @param string $namespace the namespace of the class \dana\context\ (for \dana\context\classname)
   * @param string $classname the class to load and test for
   * @param string|null $postfix the name of the filename that matches the classname (file name is prefix.postfix.php)
   * @return int|string returns the full classname (including the namespace)
   * if the script cannot be found returns 0,
   * if the class & namespace not exists returns -1
   */
  static public function getClassScriptFile(string $prefix, string $namespace, string $classname, ?string $postfix = null): int|string {
    $ret = 0;
    if (!$postfix) {
      $postfix = $classname;
    }
    $filename = strtolower("{$prefix}.{$postfix}.php");
    $cd = __DIR__ . DIRECTORY_SEPARATOR;
    self::WriteDebugClassFile('Loading: ' . $cd . "{$filename}\n");
    if (file_exists($filename)) {
      require_once $filename;
      $fullClassName = $namespace . ucwords($classname);
      if (class_exists($fullClassName)) {
        $ret = $fullClassName;
      } else {
        $ret = -1; // no class / incorrect namespace
        self::WriteDebugClassFile("Filename: {$filename}\nMissing Class: {$fullClassName}\n");
      }
    } else {
      self::WriteDebugClassFile('DIR: ' . __DIR__ . "\n");
      self::WriteDebugClassFile("Missing Filename: {$cd}{$filename}\nClass: {$namespace}{$classname}\n");
    }
    return $ret;
  }

  /**
   * Remove any empty elements in the $list array
   *
   * @param string|array $list
   *
   * @return array
   */
  static public function removeEmptyElements(string|array $list): array {
    if (is_array($list)) {
      foreach($list as $key => $line) {
        if (empty($line)) {
          unset($list[$key]);
        }
      }
    }
    return $list;
  }

  /*
  static public function getYesNo(bool $value): string {
    if ($value) {
      $ret = \dana\core\translation\Translator::lookupCommonRef(
        \dana\core\translation\Translator::TT_COMMON_YES
      );
    } else {
      $ret = \dana\core\translation\Translator::lookupCommonRef(
        \dana\core\translation\Translator::TT_COMMON_NO
      );
    }
    return $ret;
  }

  static public function getTrueFalse(bool $value): string {
    if ($value) {
      $ret = \dana\core\translation\Translator::lookupCommonRef(
        \dana\core\translation\Translator::TT_COMMON_TRUE
      );
    } else {
      $ret = \dana\core\translation\Translator::lookupCommonRef(
        \dana\core\translation\Translator::TT_COMMON_FALSE
      );
    }
    return $ret;
  }
*/
  static public function makeSlug(string $value): string {
    return str_replace('-', '', ucwords(strtolower($value), '-'));
  }
/*
  static public function prettyCount(int $count, string $singleContext = '', string $pluralContext = ''): string {
    if (!$pluralContext) {
      $pluralContext = $singleContext;
    }
    if ($count === 0) {
      $ret = trim(\dana\core\translation\Translator::lookupCommonRef(
          \dana\core\translation\Translator::TT_COMMON_ZERO
        ) . ' Utils.php' . $pluralContext);
    } elseif ($count === 1) {
      $ret = trim(\dana\core\translation\Translator::lookupCommonRef(
          \dana\core\translation\Translator::TT_COMMON_ONE
        ) . ' Utils.php' . $singleContext);
    } else {
      $ret = trim($count . ' ' . $pluralContext);
    }
    return $ret;
  }
*/
  static public function getValueAsInt(string $value, ?string $default = null): ?string {
    if (is_null($default)) {
      $default = $value;
    }
    return (is_numeric($value)) ? (int) $value : $default;
  }

  static public function getSimpleWord(string $word, bool $digits = true, bool $hyphen = true): string {
    if ($digits) {
      $reg = ($hyphen) ? "/[^0-9A-zÀ-ú-]+/" : "/[^0-9A-zÀ-ú]+/";
    } else {
      $reg = ($hyphen) ? "/[^A-zÀ-ú-]+/" : "/[^A-zÀ-ú]+/";
    }
    return strtolower(preg_replace($reg, "", trim(html_entity_decode($word))));
  }
}

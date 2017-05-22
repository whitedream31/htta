<?php
//namespace dana\core;

register_shutdown_function('fatal_handler');

require_once "consts.php";

function fatal_handler() {
  $error = error_get_last();
  if ($error !== null) {
//    $errno = $error["type"];
    $errfile = $error["file"];
    $errline = $error["line"];
    $errstr = $error["message"];
    echo "<p class='error'><strong>{$errstr}</strong> - {$errfile} at line {$errline}</p>\n";
    try {
      var_dump(xdebug_get_function_stack());
    } catch (Exception $e) {}
    die('Fatal Error - Terminated');
  }
}

function TryAutoLoad($file, $name) {
  $ret = file_exists($file);
  if ($ret) {
    include_once $file;
  }
  return $ret;
}

function __autoload($name) {
  $list = explode('\\', $name);
  $class = end($list);
  if (!TryAutoLoad("class.{$class}.php", $name)) {
    if (!TryAutoLoad("worker.{$class}.php", $name)) {
      if (!TryAutoLoad("class.table.{$class}.php", $name)) {
        if (!TryAutoLoad("../../scripts/class.table.{$class}.php", $name)) {
          if (!TryAutoLoad("scripts/class.table.{$class}.php", $name)) {
            echo
              "<h2 class='error'>Class '{$class}' not found</h2>\n";
            throw new \Exception("Unable to load {$name}:'{$class}'.");
          }
        }
      }
    }
  }
}

function CountToString($value, $postfix = 'item', $none = '<em>none</em>') {
  $newvalue = (is_array($value)) ? count($value) : $value;
  if ($newvalue == 0) {
    $ret = $none;
  } elseif ($newvalue == 1) {
    $ret = trim('1 ' . $postfix);
  } elseif ($postfix) {
    $ret = trim("{$newvalue} {$postfix}s");
  } else {
    $ret = $newvalue;
  }
  return $ret;
}

/**
 * Returns true if the $value is seen as blank
 * If array returns true if empty, if string returns true if empty or 'na'
 *
 * @param mixed $value
 *
 * @return bool
 */
function IsBlank($value) {
  if (is_array($value)) {
    $ret = count($value) == 0;
  } else {
    $ret = (empty($value) || strtolower($value) == 'na');
  }
  return (bool) $ret;
}

/**
 * Convert array into a string
 *
 * @param array $value
 * @param string $glue
 *
 * @return string
 */
function ArrayToString($value, $glue = "\n") {
  if (is_array($value)) {
    if (count($value)) {
      $ret = ''; //implode($glue, $value);
      foreach($value as $k => $v) {
        $ret .= ArrayToString($v, $glue) . $glue;
      }
      $ret = substr($ret, 0, -strlen($glue));
    } else {
      $ret = '';
    }
  } else {
    $ret = (string) $value;
  }
  return $ret;
}

/**
 * Convert a string into an array, separated by $separator (\n)
 *
 * @param string $value
 * @param string $separator
 *
 * @return array
 */
function StringToArray($value, $separator = "\n") {
  if (is_array($value)) {
    $ret = $value;
  } else {
    $ret = explode($separator, $value);
  }
  return $ret;
}

/**
 * Remove any empty elements in the $list array
 *
 * @param array $list
 *
 * @return array
 */
function RemoveEmptyElements($list) {
  if (is_array($list)) {
    foreach($list as $key => $line) {
      if (empty($line)) {
        unset($list[$key]);
      }
    }
  }
  return $list;
}

function MakeArray($value1, $value2 = false) {
  $ret = [];
  if (!empty($value1)) {
    if (is_array($value1)) {
      $ret = array_merge($ret, $value1);
    } else {
      $ret[] = $value1;
    }
  }
  if ($value2) {
    $ret = array_merge($ret, MakeArray($value2));
  }
  return $ret;
}

/**
 * GetGet returns the $_GET element using filter_input for security
 *
 * @param string $name
 * @param mixed $default is returned if element not found
 *
 * @return mixed
 */
function GetGet($name, $default = false) {
  $ret = filter_input(INPUT_GET, $name, FILTER_SANITIZE_SPECIAL_CHARS);
  if ($ret === NULL) {
    $ret = $default;
  }
  return $ret;
}

/**
 * GetPost returns the $_POST element using filter_input for security
 *
 * @param string $name
 * @param mixed $default is returned if element not found
 *
 * @return mixed
 */
function GetPost($name, $default = false) {
  $ret = filter_input(INPUT_POST, $name, FILTER_SANITIZE_SPECIAL_CHARS);
  if ($ret === NULL) {
    $ret = $default;
  }
  return $ret;
}

function PostExists($name) {
  return filter_input(INPUT_POST, $name, FILTER_SANITIZE_SPECIAL_CHARS) !== null;
}

/**
 * GetServerVar returns the $_SERVEER element using filter_input for security
 *
 * @param string $name
 * @param mixed $default is returned if element not found
 *
 * @return mixed
 */
function GetServerVar($name, $default = false) {
  $ret = filter_input(INPUT_SERVER, $name, FILTER_SANITIZE_SPECIAL_CHARS);
  if ($ret === NULL) {
    $ret = $default;
  }
  return $ret;
}

/**
 * IfBlank returns $default if $value is blank, else returns $value
 * blank if $value is array and empty, is numeric and 0, is string and empty or 'na'
 *
 * @param mixed $value
 * @param mixed $default
 *
 * @return mixed
 */
function IfBlank($value, $default) {
  return (IsBlank($value)) ? $default : $value;
}

/**
 * ShowDebugValue shows $value for debugging only
 * - should be removed for production
 *
 * @param string $label
 * @param mixed $value
 */
function ShowDebugValue($label, $value, $exit = false) {
  echo "<p class='error'>{$label} = </p>[<pre>";
  print_r($value);
  echo "</pre>]";
  if ($exit) {
    exit;
  }
}

/**
 * ShowDebugStep shows incremental value for debugging only (to see the process flow of code)
 * - should be removed for production
 */
function ShowDebugStep($counter) {
  ShowDebugValue('STEP ', $counter);
}

function ShowDebugMessage($msg) {
  echo "<p class='error'>{$msg}</p>" . CRNL;
}

function ShowDebugTableField($table, $field, $label = false) {
  if ($table) {
    if ($field) {
      $value = $table->GetFieldValue($field);
      if (!$label) {
        $label = $field;
      }
      echo "<p class='error'>{$label} = '{$value}'</p>" . CRNL;
    } else {
      echo "<p class='error'>Field '{$field}' not assigned</p>" . CRNL;
    }
  } else {
    echo "<p class='error'>Table '{$table}' not assigned</p>" . CRNL;
  }
}

 /**
  * Get relative time from a time value to a pretty English textdomain
  *
  * @param time $starttime
  * @param time $endtime (optional)
  *
  * @return string
  */
function RelativeTime($starttime, $endtime = false) {
  $start = (!ctype_digit($starttime))
    ? strtotime($starttime) : $starttime;
  $finish = ($endtime === false)
    ? time() : $endtime;
  $diff = $finish - $start;
  if($diff == 0) {
    $ret = 'now';
  } elseif($diff > 0) {
    $day_diff = floor($diff / 86400);
      if($diff < 60) {
        $ret = 'just now';
      } elseif($diff < 120) {
        $ret = '1 minute ago';
      } elseif($diff < 3600) {
        $ret = floor($diff / 60) . ' minutes ago';
      } elseif($diff < 7200) {
        $ret = '1 hour ago';
      } elseif($diff < 86400) {
        $ret = floor($diff / 3600) . ' hours ago';
      } elseif($day_diff == 1) {
        $ret = 'Yesterday';
      } elseif($day_diff < 7) {
        $ret = $day_diff . ' days ago';
      } elseif($day_diff < 31) {
        $ret = floor($day_diff / 7) . ' weeks ago';
      } elseif($day_diff < 90) {
        $ret = 'last month';
      } else {
        $ret = floor($day_diff / 30) . ' months ago';
      }
  } else {
    $diff = abs($diff);
    $day_diff = floor($diff / 86400);
    if($day_diff == 0) {
      if($diff < 120) {
        $ret = 'in a minute';
      } elseif($diff < 3600) {
        $ret = 'in ' . floor($diff / 60) . ' minutes';
      } elseif($diff < 7200) {
        $ret = 'in an hour';
      } elseif($diff < 86400) {
        $ret = 'in ' . floor($diff / 3600) . ' hours';
      }
    } elseif($day_diff == 1) {
      $ret = 'Tomorrow';
    } elseif($day_diff < 4) {
      $ret = date('l', $start);
    } elseif($day_diff < 7 + (7 - date('w'))) {
      $ret = 'next week';
    } elseif(ceil($day_diff / 7) < 4) {
      $ret = 'in ' . ceil($day_diff / 7) . ' weeks';
    } elseif(date('n', $start) == date('n') + 1) {
      $ret = 'next month';
    } else {
      $ret = date('F Y', $start);
    }
  }
  return $ret;
}

function GetSessionValue($session) {
  return (isset($_SESSION[$session])) ? $_SESSION[$session] : false;
}

function UnsetSessionValue($session) {
  if (isset($_SESSION[$session])) {
    unset($_SESSION[$session]);
  }
}

function GetItemFromArray($list, $key, $default = false) {
  return (isset($list[$key])) ? $list[$key] : $default;
}

function RedirectPage($url) {
  header('Location: ' . $url);
  exit();
}

function BuildURL($script, $querylist) {
  $ret = $script;
  if (is_array($querylist) && count($querylist)) {
    $ret .= '?';
    foreach($querylist as $k => $v) {
      $ret .= $k . '=' . $v;
    }
  }
  return $ret;
}

function IncludeTrailingPathDelimiter($path, &$slash = false) {
  if (is_array($path)) {
    $ret = '';
    foreach($path as $p) {
      if ($p) {
        $ret .= IncludeTrailingPathDelimiter($p, $slash);
      }
    }
  } else {
    if (!$slash) {
      if (strpos($path, '\\') !== false) {
        $slash = '\\';
      } elseif (strpos($path, '/') !== false) {
        $slash = '/';
      } else {
        $slash = DIRECTORY_SEPARATOR;
      }
    }
    $exists = (substr($path, -1) == '\\') || (substr($path, -1) == '/');
    $ret = $path . ($exists ? '' : $slash);
  }
  return $ret;
}

function ExcludeTrailingSlash($path) {
  return rtrim($path, '/\\');
}

function EnsureTrailingPathDelimiter($path, $slash = '/') {
  if (!$slash) {
    $slash = DIRECTORY_SEPARATOR;
  }
  return str_replace('\\', $slash, $path);
}

function ValidatePathDelimiter($path, $slash = false) {
  if (!$slash) {
    if (strpos($path, '\\') !== false) {
      $slash = '\\';
    } elseif (strpos($path, '/') !== false) {
      $slash = '/';
    } else {
      $slash = DIRECTORY_SEPARATOR;
    }
  }
  $otherslash = ($slash == '\\') ? '/' : '\\';
  $ret = str_replace($otherslash, $slash, $path);
  return str_replace($slash . $slash, $slash, $ret);
}

function DebugShowVarList($list, $heading = 'Var List', $quit = false) {
  echo "<h3>{$heading}</h3>";
  foreach ($list as $k => $v) {
    if (is_object($v)) {
      $v = get_class($v);
    }
    echo "<p><strong>{$k}</strong>: {$v}</p>";
  }
  if ($quit) {
    exit;
  }
}

function GetPlural($value, $text) {
  return ($value == 0)
    ? 'zero'
    : (($value > 1)
      ? $text . 's'
      : $text);
}

function GetCoreMode($modetype = \dana\table\coremode::MODETYPE_LIVE) {
  require_once 'class.table.coremode.php';
  $coremode = \dana\table\coremode::$instance;
  if (!$coremode) {
     $coremode = \dana\table\coremode::GetModeByType($modetype);
  }
  return $coremode;
}

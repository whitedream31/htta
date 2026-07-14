<?php

namespace HTTA\Lib;

/**
 * static class library for file / path / url handling
 *
 * @author ians
 */
class File {

  /**
   * add slashes at the end of the $path
   * if $path is an array it will return a string with slashes between each item
   * @param string|array $path the value to add slashes
   * @param string|null $slash is the back or forward slash to use for separating directory names
   * @return string
   */
  static public function IncludeTrailingPathDelimiter(array|string $path, string $slash = null): string {
    if (is_array($path)) {
      $ret = '';
      foreach($path as $p) {
        if ($p && $p !== SLASH) {
          $ret .= self::IncludeTrailingPathDelimiter($p, $slash);
        }
      }
    } else {
      if (!$slash) {
        if (str_contains($path, '\\')) {
          $slash = '\\';
        } elseif (str_contains($path, '/')) {
          $slash = '/';
        } else {
          $slash = SLASH; // DIRECTORY_SEPARATOR;
        }
      }
      $exists = (str_ends_with($path, '\\')) || (str_ends_with($path, '/'));
      $ret = $path . ($exists ? '' : $slash);
    }
    return $ret;
  }

  static public function EnsureTrailingPathDelimiter(string $path, string $slash = '/'): string {
    if (!$slash) {
      $slash = DIRECTORY_SEPARATOR;
    }
    return str_replace('\\', $slash, $path);
  }

  static public function EnsureDirectoryExists(string $path): bool {
    return is_dir($path) || mkdir($path, 0775, true); // or 0777?
  }

  static public function ExcludeTrailingSlash(string $path): string {
    return rtrim(rtrim($path, '/\\'), '/');
  }

  /**
   * physically delete the specified file
   * @param string $filename
   * @return boolean - true if successful, false if f
   */
  static public function DeleteFile(string $filename): bool {
    return file_exists($filename) && unlink($filename);
  }

  /**
   * Empty all files in path
   * @param string $path location of directory
   * @param array|null $extlist only files with list of file extensions are deleted
   * @return int
   */
  static public function EmptyDirectory(string $path, ?array $extlist = null) {
    $count = 0;
    if (is_dir($path)) {
      $scanPath = File::ExcludeTrailingSlash($path);
      $objects = scandir($scanPath);
      $itemPath = File::IncludeTrailingPathDelimiter($scanPath);
      foreach ($objects as $object) {
        if ($object !== "." && $object !== "..") {
          $itm = $itemPath . $object;
          if (is_dir($itm)) {
            $count += File::EmptyDirectory($itm); // delete folder (recursive call)
          } elseif (is_array($extlist)) {
            $ext = pathinfo($itm, PATHINFO_EXTENSION);
            if (in_array($ext, $extlist)) {
              if (File::DeleteFile($itm)) { // delete file
                $count++;
              }
            }
          } elseif (File::DeleteFile($itm)) { // delete file
            $count++;
          }
        }
      }
    }
    return $count;
  }

}

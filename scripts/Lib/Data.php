<?php

namespace HTTA\Lib;

/**
 * static class library for data display and formatting
 *
 * @author ians
 */
class Data {

  /**
   * flattens a list to a string, includes adding a value between each item
   * - ignores any empty items
   * - similar to ArrayToString except the list must be an array of strings and there is no gap at the end)
   *
   * @param array $list
   * @param string $gap the value between each item (except the end)
   *
   * @return string
   */
  static public function listToSingleLine(array $list, string $gap = ', '): string {
    $ret = '';
    $count = count($list);
    $max = $count-1;
    $item = reset($list);
    for($lp = 0; $lp < $count; $lp++) {
      if (trim($item)) {
        $ret .= $item;
        if ($lp < $max) {
          $ret .= $gap;
        }
      }
      $item = next($list);
    }
    return $ret;
  }

/**
  * convert a list into an unordered html list
  * - list must be an array of strings
  *
  * @param array $list
  * @return string
*/
  static public function listToUnorderedList(array $list): string {
    $ret = '<ul>';
    foreach($list as $item) {
      $ret .= "<li>{$item}</li>";
    }
    return $ret . '</ul>';
  }

  static public function makeIcon(string $icon, bool $rightPadding = false, bool $leftPadding = false): string {
    $class = '';
    if ($leftPadding) {
      $class = ' ps-2';
    }
    if ($rightPadding) {
      $class .= ' pe-2';
    }
    return "<i class='{$icon}{$class}'></i>";
  }

  // internal method to include class attribute in element
  static public function getClassAttr(string|array $class): string {
    $classname = \dana\core\lib\Utils::arrayToString($class);
    return ($classname) ? " class='{$classname}'" : '';
  }

  // create a anchor tag using parameters
  static public function makeLink(string $description, string $url, ?string $classname, array $attrs = []): ?string {
    $ret = false;
    if ($url) {
      $attrs['href'] = self::ensureURLHasProtocol($url);
      $ret = self::makeElement('a', $classname, $attrs, $description);
    }
    return $ret;
  }

  // create a html tag
  static public function makeElement(
    ?string $element,
    ?string $classname = null,
    string|array|null $attrs = null,
    string|array|null $value = null,
    bool $selfClosing = false
  ): string {
    if ($element) {
      $class = ($classname) ? self::getClassAttr($classname) : '';
      if (is_array($attrs)) {
        $attr = ' ' . self::getAttributes($attrs);
      } elseif (is_null($attrs)) {
        $attr = '';
      } else {
        $attr = ' ' . (string) $attrs; // attrs could be a workerattr object, run toString
      }
      $content = ((is_null($value)) ? '' : (is_array($value))) ? \dana\core\lib\Utils::arrayToString($value) : $value;
      $ret = ($selfClosing)
        ? "<{$element}{$class}{$attr}>"
        : "<{$element}{$class}{$attr}>{$content}</{$element}>";
    } else {
      $ret = $value;
    }
    return $ret;
  }

  // internal method to convert key->value pair array entries into a key='value'... string
  static public function getAttributes(array $attrs): string {
    $ret = '';
    foreach($attrs as $attrKey => $attrValue) {
      $ret .= ($attrValue === false) ? " {$attrKey}" : " {$attrKey}=\"{$attrValue}\"";
    }
    return $ret;
  }

  // make sure the http protocol is included
  static public function ensureURLHasProtocol(string $url): string {
    return preg_replace('/^(?!https?:\/\/)/', 'https://', $url);
  }

  // remove the http(s) from the url
  static public function removeProtocolFromURL(string $url): string {
    // in case scheme relative URI is passed, e.g., //www.google.com/
    $input = trim($url, '/');
    // if scheme not included, prepend it
    if (!preg_match('#^http(s)?://#', $input)) {
      $input = 'http://' . $input;
    }
    $urlParts = parse_url($input);
    // remove www
    return preg_replace('/^www\./', '', $urlParts['host']);
  }

  // convert a string to url friendly (no spaces etc and lower case)
  static public function stringToURL(string $url): string {
    $url1 = preg_replace('~[^\\pL0-9_]+~u', '', $url);
    $url2 = trim($url1, "-");
    $url3 = iconv("utf-8", "us-ascii//TRANSLIT", $url2);
    $url4 = strtolower($url3);
    return preg_replace('~[^-a-z0-9_]+~', '', $url4);
  }
}

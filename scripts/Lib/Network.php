<?php

namespace HTTA\Lib;

/**
 * static class library for network handling
 *
 * @author ians
 */
class Network {

  static public function getIPAddress() {
    return (Utils::getServer('HTTP_CLIENT_IP'))
      ? Utils::getServer('HTTP_CLIENT_IP')
      : ((Utils::getServer('HTTP_X_FORWARDED_FOR'))
        ? Utils::getServer('HTTP_X_FORWARDED_FOR')
        : Utils::getServer('REMOTE_ADDR'));
  }

  static public function getUserAgent() {
    return Utils::getServer('HTTP_USER_AGENT');
  }

}

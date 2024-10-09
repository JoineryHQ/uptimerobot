<?php

namespace Uptimerobot;

/**
 * Description of Util
 *
 * @author as
 */
class Util {

  const monitorStatuses = [
    '0' => 'paused',
    '1' => 'not checked yet',
    '2' => 'up',
    '8' => 'seems down',
    '9' => 'down',
  ];
  
  /**
   * For a given monitor, determine the duration of a current 'down' status
   * @param Array $monitor, as returned (with logs) by UptimerobotApi 'getMonitors' method.
   * 
   * @return Int If monitor is not down, -1; otherwise, number of seconds monitor has been down.
   */
  public static function getDownDuration($monitor) {
    if ($monitor['status'] != '9') {
      // Monitor is not down; return special value '-1'
      return -1;
    }
    if (!is_array($monitor['logs'])) {
      throw new \Exception('Provided monitor data does not contain a logs array.');
    }
    $downDurationsByDatetime = [];
    foreach ($monitor['logs'] as $log) {
      if ($log['type'] == '1') {
        $downDurationsByDatetime[$log['datetime']] = $log['duration'];
      }
    }
    ksort($downDurationsByDatetime);
    return array_pop($downDurationsByDatetime);
  }

  public static function getMonitorIds() {
    return array_keys(self::getMonitors());
  }

  public static function getMonitors() {
    return CONFIG['MONITORS'];
  }

  /**
   * Print a given array as a tab-delimited line.
   *
   * @param Array $line The contents of the line.
   * @param Resource $ioStream The io stream resource to print to (STDERR or STDOUT)
   */
  public static function printLine($line, $ioStream = STDERR) {
    fwrite($ioStream, implode("\t", $line) . PHP_EOL);
  }
  public static function getMonitorStatusIdLabel($statusId) {
    return (self::monitorStatuses[$statusId] ?? "Unrecognized status id: $statusId");
  }
}

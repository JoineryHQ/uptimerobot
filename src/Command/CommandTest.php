<?php

namespace Uptimerobot\Command;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use \Uptimerobot\Util;

class CommandTest extends Command {

  public function __construct() {
    parent::__construct('test', [$this, 'handle']);
    $this->setDescription('Test configuration and system.');
    
    $optAll = new \GetOpt\Option('m', 'monitors', \GetOpt\GetOpt::NO_ARGUMENT);
    $optAll->setDescription('Also test access to all configured monitors');
    $this->addOption($optAll);
  }

  public function handle(GetOpt $getOpt) {
    Util::printLine(['Testing configuration ...']);
    $tests = [];
    $tests['System has "sendemail" executable installed?'] = is_executable(trim(shell_exec("command -v sendemail")));
    $tests['NOTIFY:EMAIL_TO is configured and has good syntax?'] = (
      !empty(CONFIG['NOTIFY']['EMAIL_TO'])
      && filter_var(CONFIG['NOTIFY']['EMAIL_TO'], FILTER_VALIDATE_EMAIL)
    );
    $tests['NOTIFY:EMAIL_FROM is configured and has good syntax?'] = (
      !empty(CONFIG['NOTIFY']['EMAIL_FROM'])
      && filter_var(CONFIG['NOTIFY']['EMAIL_FROM'], FILTER_VALIDATE_EMAIL)
    );
    $tests['NOTIFY:SMTP_SERVER is configured and has good syntax?'] = (
      !empty(CONFIG['NOTIFY']['SMTP_SERVER'])
      && filter_var('smtp://' . CONFIG['NOTIFY']['SMTP_SERVER'], FILTER_VALIDATE_URL)
    );
    $tests['NOTIFY:SMTP_USERNAME is configured?'] = (
      !empty(CONFIG['NOTIFY']['SMTP_USERNAME'])
      && is_string(CONFIG['NOTIFY']['SMTP_USERNAME'])
    );
    $tests['NOTIFY:SMTP_PASSWORD is configured?'] = (
      !empty(CONFIG['NOTIFY']['SMTP_PASSWORD'])
      && is_string(CONFIG['NOTIFY']['SMTP_PASSWORD'])
    );
    $tests['MAX_REBOOT_PER_DOWNTIME is configured and has good syntax?'] = (
      !empty(CONFIG['DEFAULTS']['MAX_REBOOT_PER_DOWNTIME'])
      && filter_var(CONFIG['DEFAULTS']['MAX_REBOOT_PER_DOWNTIME'], FILTER_VALIDATE_INT)
    );
    $tests['MIN_REBOOT_INTERVAL_SECONDS is configured and has good syntax?'] = (
      !empty(CONFIG['DEFAULTS']['MIN_REBOOT_INTERVAL_SECONDS'])
      && filter_var(CONFIG['DEFAULTS']['MIN_REBOOT_INTERVAL_SECONDS'], FILTER_VALIDATE_INT)
    );    
    $tests['UPTIMROBOT_API:KEY is configured and valid?'] = $this->testApiKey();
    
    foreach ($tests as $description => $status) {
      $line = [($status ? 'PASS' : 'FAIL') . ':', $description];
      Util::printLine($line);
    }
    if (count($tests) != count(array_filter($tests))) {
      Util::printLine(['!!! FAILURES !!! See above.']);
    }
    else {
      Util::printLine(['All tests passed.']);
    }

    $badMonitorIds = [];
    if($getOpt->getOption('m') && is_array(CONFIG['MONITORS'])) {
      Util::printLine(['Testing configured monitors ...']);
      $uptimerobot = \Uptimerobot\UptimerobotApi::singleton();
      foreach (CONFIG['MONITORS'] as $monitorId => $monitor) {
        $response = $uptimerobot->request('getMonitors', ['monitors' => $monitorId]);
        if (count($response['monitors']) != 1) {
          $badMonitorIds[] = $monitorId;
        }
      }
      if (!empty($badMonitorIds)) {
        foreach ($badMonitorIds as $badMonitorId) {
          Util::printLine(['Not found in UptimeRobot: Configured monitor ID:', $badMonitorId]);
        }
        Util::printLine(['!!! MISCONFIGURED MONITORS FOUND !!! See above.']);
      }
      else {
        Util::printLine(['All configured monitors found in UptimeRobot.']);
      }
    }

    return;
  }
  
  private function testApiKey() {
    $ret = FALSE;
    if (!empty(CONFIG['UPTIMROBOT_API']['KEY'])) {
      $uptimerobot = \Uptimerobot\UptimerobotApi::singleton();
      try {
        $response = $uptimerobot->request('getMonitors', ['monitors' => '']);
        if($response['stat'] == 'ok') {
          $ret = TRUE;
        }
      }
      catch (\Exception $e) {
        // Nothing to do here; api request failed, so this test fails.
      }
    }
    return $ret;
  }
}

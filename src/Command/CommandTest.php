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
    $tests = [];

    $tests['System has "sendemail" executable installed?'] = is_executable(trim(shell_exec("command -v sendemail")));
    $tests['NOTIFY_EMAIL is configured and has good syntax'] = (
      !empty(CONFIG['DEFAULTS']['NOTIFY_EMAIL'])
      && filter_var(CONFIG['DEFAULTS']['NOTIFY_EMAIL'], FILTER_VALIDATE_EMAIL)
    );
    $tests['MAX_REBOOT_PER_DOWNTIME is configured and has good syntax'] = (
      !empty(CONFIG['DEFAULTS']['MAX_REBOOT_PER_DOWNTIME'])
      && filter_var(CONFIG['DEFAULTS']['MAX_REBOOT_PER_DOWNTIME'], FILTER_VALIDATE_INT)
    );
    $tests['MIN_REBOOT_INTERVAL is configured and has good syntax'] = (
      !empty(CONFIG['DEFAULTS']['MIN_REBOOT_INTERVAL'])
      && filter_var(CONFIG['DEFAULTS']['MIN_REBOOT_INTERVAL'], FILTER_VALIDATE_INT)
    );    
    $tests['UPTIMROBOT_API:KEY is configured and valid'] = $this->testApiKey();    
    
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

    if($getOpt->getOption('m')) {
      
    }

    return;
  }
  
  private function testApiKey() {
    $ret = FALSE;
    if (!empty(CONFIG['UPTIMROBOT_API']['KEY'])) {
      $uptimerobot = new \Uptimerobot\UptimerobotApi(CONFIG['UPTIMROBOT_API']['KEY']);
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

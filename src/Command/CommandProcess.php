<?php

namespace Uptimerobot\Command;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use \Uptimerobot\Util;

class CommandProcess extends Command {

  public function __construct() {
    parent::__construct('process', [$this, 'handle']);
    $this->setDescription('Process uptimerobot monitors in config: take appropriate action per monitor status.');

//    $this->addOperands([
//        Operand::create('file', Operand::REQUIRED)
//        ->setValidation('is_readable'),
//        Operand::create('destination', Operand::REQUIRED)
//        ->setValidation('is_writable')
//    ]);

//    $optAll = new \GetOpt\Option('a', 'all', \GetOpt\GetOpt::NO_ARGUMENT);
//    $optAll->setDescription('List all existing monitors (not just those in config)');
//    $this->addOption($optAll);
    
//    $optStatus = new \GetOpt\Option('s', 'status', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
//    $monitorStatuses = [];
//    foreach (Util::monitorStatuses as $statusId => $statusLabel) {
//      $monitorStatuses[] = "$statusId: $statusLabel";
//    }
//    $monitorStatusesHelp = implode('; ', $monitorStatuses);
//    $optStatus->setDescription('INTEGER: Limit list to monitors with a given status: ' . $monitorStatusesHelp);
//    $optStatus->setValidation(function ($value) {
//        return (array_key_exists($value, Util::monitorStatuses));
//    }, 'Status must be a valid status integer, one of: '. $monitorStatusesHelp);
//    $this->addOption($optStatus);
  }

  public function handle(GetOpt $getOpt) {
    $configMonitorIds = Util::getMonitorIds();
    $configMonitors = Util::getMonitors();
    $uptimerobot = \Uptimerobot\UptimerobotApi::singleton();

    $response = $uptimerobot->request('getMonitors', ['logs' => 1, 'monitors' => implode('-', $configMonitorIds)]);

    foreach($response['monitors'] as $monitor) {
      $statusLabel = Util::getMonitorStatusIdLabel($monitor['status']);
      if ($statusLabel == 'down') {
        Util::sendNotificationEmail($monitor, $statusLabel);
      }
    }
  }
}

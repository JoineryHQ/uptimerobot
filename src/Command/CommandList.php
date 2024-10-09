<?php

namespace Uptimerobot\Command;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use \Uptimerobot\Util;

class CommandList extends Command {

  public function __construct() {
    parent::__construct('list', [$this, 'handle']);
    $this->setDescription('List uptimerobot monitors (by default, limit to monitors in config.)');

//    $this->addOperands([
//        Operand::create('file', Operand::REQUIRED)
//        ->setValidation('is_readable'),
//        Operand::create('destination', Operand::REQUIRED)
//        ->setValidation('is_writable')
//    ]);
    $optAll = new \GetOpt\Option('a', 'all', \GetOpt\GetOpt::NO_ARGUMENT);
    $optAll->setDescription('List all existing monitors (not just those in config)');
    $this->addOption($optAll);
    
    $optStatus = new \GetOpt\Option('s', 'status', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
    $monitorStatuses = [];
    foreach (Util::monitorStatuses as $statusId => $statusLabel) {
      $monitorStatuses[] = "$statusId: $statusLabel";
    }
    $monitorStatusesHelp = implode('; ', $monitorStatuses);
    $optStatus->setDescription('INTEGER: Limit list to monitors with a given status: ' . $monitorStatusesHelp);
    $optStatus->setValidation(function ($value) {
        return (array_key_exists($value, Util::monitorStatuses));
    }, 'Status must be a valid status integer, one of: '. $monitorStatusesHelp);
    $this->addOption($optStatus);
  }

  public function handle(GetOpt $getOpt) {
    $configMonitorIds = Util::getMonitorIds();
    $configMonitors = Util::getMonitors();
    $uptimerobot = new \Uptimerobot\UptimerobotApi(CONFIG['UPTIMROBOT_API']['KEY']);

    
    $optStatus = $getOpt->getOption('s');
    $statuses = $optStatus ?? NULL;
    $optAll = $getOpt->getOption('a');
    if ($optAll) {
      $response = $uptimerobot->request('getMonitors', ['statuses' => $statuses]);
    }
    else {
      $response = $uptimerobot->request('getMonitors', ['logs' => 1, 'monitors' => implode('-', $configMonitorIds), 'statuses' => $statuses]);
    }
    
    $columnLabels = [
      'id', 
      'friendly_name', 
      'configured',
      'LINODE_LABEL',
      'status',
    ];
    Util::printLine($columnLabels);

    foreach($response['monitors'] as $monitor) {
      $id = $monitor['id'];
      $out = [
        $id, 
        $monitor['friendly_name'], 
        (in_array($id, $configMonitorIds) ? 'TRUE' : 'FALSE'),
        ($configMonitors[$id]['LINODE_LABEL'] ?? 'NULL'),
        Util::printMonitorStatus($monitor['status']),
      ];
      Util::printLine($out);
    }
  }
}

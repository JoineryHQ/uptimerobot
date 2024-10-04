#!/usr/bin/php
<?php

// require config.php or die with error
require(__DIR__ . '/config.php');
require(__DIR__ . '/class/UptimerobotApi.php');
require(__DIR__ . '/class/Util.php');
require(__DIR__ . '/class/Argo.php');

//var_dump($argv); 
//var_dump(getopt('h'));
//exit;


$actions = [
  'process' => [
    ['h', 'help', '', 'Print help for "process" action', 'Util::helpProcess'],
  ],
  'list' => [
    ['h', 'help', '', 'Print help for "list" action', 'helpList'],    
  ],
  NULL => [
    ['h', 'help', '', 'Print help', 'help'],    
  ],
];

$argo = new Argo($actions);
$action = $argo->getAction();

$callback = $argo->runCallback();
exit;


var_dump($argc);
var_dump($argv);
$opts = getopt('abc', ['one::', 'two', 'three']);
var_dump($opts);

exit;

// validate config

// get all monitors with status '9' (down)
  // Look for a log with type = '1' (down)
  // 
$monitors = implode('-', Util::getMonitorIds());

$uptimerobot = new UptimerobotApi(CONFIG['UPTIMROBOT_API']['KEY']);
//$response = $uptimerobot->request('getMonitors', ['logs' => 1, 'statuses' => '9', 'monitors' => $monitors]);
$response = $uptimerobot->request('getMonitors');

foreach ($response['monitors'] as $monitor) {
  echo "monitor: {$monitor['id']} ({$monitor['friendly_name']})\n";
}
exit;

foreach ($response['monitors'] as $monitor) {
  echo "monitor: {$monitor['id']} ({$monitor['friendly_name']})\n";
  $downDuration = Util::getDownDuration($monitor);
  echo "  down duration: ". $downDuration . "\n";
  
}

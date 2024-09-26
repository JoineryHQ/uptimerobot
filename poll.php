#!/usr/bin/php
<?php

# require config.php or die with error
require(__DIR__ . '/config.php');

# validate config

// API reference: https://uptimerobot.com/api/#getMonitorsWrap
// monitor 'status': 0=paused; 2=up; 9=down (but probably better to check for logs of 'type', per 'log>type' in linked docs above)
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.uptimerobot.com/v2/getMonitors",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "api_key=" . CONFIG['UPTIMROBOT_API_KEY'] . "&format=xml&logs=1",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: application/x-www-form-urlencoded"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
}
else {
  echo $response;
}

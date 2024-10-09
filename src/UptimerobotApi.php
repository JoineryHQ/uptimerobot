<?php

namespace Uptimerobot;

/**
 * Class API
 * @package UptimeRobot
 */
class UptimerobotApi {

  private $apiKey;

  private static $_singleton;

  public $debug;

  /**
   * Initializes the API.
   *
   * @param array $config An array of configuration
   * @param array $options An array of options for curl
   *
   * @throws \Exception Configuration is missing
   */
  private function __construct() {
    $apiKey = CONFIG['UPTIMROBOT_API']['KEY'];
    if (empty($apiKey)) {
      throw new \Exception('Missing API Key');
    }
    $this->apiKey = $apiKey;
  }

  /**
   * Singleton pattern.
   *
   * @see __construct()
   *
   * @return object This
   */
  public static function singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new UptimerobotApi();
    }
    return self::$_singleton;
  }

  /**
   * Makes curl call to the ENDPOINT & returns output.
   *
   * @param string $method The resource of the api
   * @param array $params Array of options for the query query
   *
   * @return array json_decoded contents
   * @throws \Exception If the curl request fails
   */
  public function request($method, $params = []) {
    $curl = curl_init();

    $urlArgs = http_build_query($this->buildParams($params));

    curl_setopt_array($curl, [
      CURLOPT_URL => "https://api.uptimerobot.com/v2/" . $method,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => $urlArgs,
      CURLOPT_HTTPHEADER => [
        "cache-control: no-cache",
        "content-type: application/x-www-form-urlencoded"
      ],
    ]);

    $response = curl_exec($curl);
    $this->setDebug($curl, $response);

    if (curl_errno($curl) > 0) {
      throw new \Exception('There was an error while making the request. Request said: '. $this->debug['errorNum'] . ': '. $this->debug['error']);
    }
    curl_close($curl);

    $responseArray = json_decode($response, TRUE);

    if (is_null($responseArray)) {
      throw new \Exception('Unable to decode JSON response');
    }

    if (!empty($responseArray['stat']) && $responseArray['stat'] != 'ok' ) {
      throw new \Exception('API returned an error: stat: '. $responseArray['stat'] . '; type: '. $responseArray['error']['type'] . '; message: '. $responseArray['error']['message']);
    }
    return $responseArray;
  }

  private function buildParams($params) {
    $defaultParams = [
      'api_key' => $this->apiKey,
      'format' => 'json',
    ];
    return array_merge($defaultParams, $params);
  }


  /**
   * Sets debug information from last curl.
   *
   * @param resource $curl Curl handle
   */
  private function setDebug($curl, $response) {
    $this->debug = [
      'errorNum' => curl_errno($curl),
      'error' => curl_error($curl),
      'info' => curl_getinfo($curl),
      'raw' => $response,
    ];
  }
}

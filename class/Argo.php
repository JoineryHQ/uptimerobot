<?php

/**
 * Utility class for argument handling
 *
 * @author as
 */
class Argo {
  private $config = [];
  private $action = NULL;
  private $supportedOpts = [];
  private $givenOpts = [];
  
  public function __construct($config) {
    $this->config = $config;

    $opts = getopt('a:', ['action:']);
    $this->action = $opts['a'] ?? $opts['action'] ?? NULL;
    $this->supportedOpts = $config[$this->action] ?? [];
    
    $shortOptions = '';
    $longOptions = [];
    foreach ($this->supportedOpts as $arg) {
      [$short, $long, $suffix, $description, $callback] = $arg;
      if (!empty($short)) {
        $shortOptions .= "{$short}{$suffix}";
      }
      if (!empty($long)) {
        $longOptions[] = "{$long}{$suffix}";
      }
    }
    $this->givenOpts = getopt($shortOptions, $longOptions, $restIndex);
    echo "\$restIndex: $restIndex\n";
    global $argc;
    echo "\$argc: " . var_export($argc, 1) . "\n";
    global $argv;
    echo "\$argv: " . var_export($argv, 1) . "\n";
  }
  
  public function getAction() {
    return $this->action;
  }
  
  public function runCallback() {
    $callback = NULL;
    foreach ($this->givenOpts as $givenOpt => $value) {
      foreach ($this->supportedOpts as $supportedOpt) {
        if (
          ($callback = $supportedOpt[4]) 
          && ($supportedOpt[0] == $givenOpt || $supportedOpt[1] == $givenOpt)
          && is_callable($callback)
        ) {
          call_user_func($callback);
          return;
        }
      }
    }
  }
  
  
  public static function arg($argName = "", $default = NULL) {

    static $argtext = "";
    static $arginfo = [];

    /* helper */ $contains = function ($h, $n) {
      return (false !== strpos($h, $n));
    };
    /* helper */ $valuesOf = function ($s) {
      return explode(",", $s);
    };

    //  called with a multiline string --> parse arguments
    if ($contains($argName, "\n")) {

      //  parse multiline text input
      $argtext = $argName;
      $args = $GLOBALS["argv"] ?: [];
      $rows = preg_split('/\s*\n\s*/', trim($argName));
      $data = $valuesOf("char,word,type,help");
      foreach ($rows as $row) {
        list($char, $word, $type, $help) = preg_split('/\s\s+/', $row);
        $char = trim($char, "-");
        $word = trim($word, "-");
        $key = $word ?: $char ?: "";
        if ($key === "")
          continue;
        $arginfo[$key] = compact($data);
        $arginfo[$key]["value"] = NULL;
      }

      $nr = 0;
      while ($args) {

        $argName = array_shift($args);
        if ($argName[0] <> "-") {
          $arginfo[$nr++]["value"] = $argName;
          continue;
        }
        $argName = ltrim($argName, "-");
        $v = NULL;
        if ($contains($argName, "="))
          list($argName, $v) = explode("=", $argName, 2);
        $k = "";
        foreach ($arginfo as $k => $arg)
          if (($arg["char"] == $argName) || ($arg["word"] == $argName))
            break;
        $t = $arginfo[$k]["type"];
        switch ($t) {
          case "bool" : $v = true;
            break;
          case "str" : if (is_null($v))
              $v = array_shift($args);
            break;
          case "int" : if (is_null($v))
              $v = array_shift($args);
            $v = intval($v);
            break;
        }
        $arginfo[$k]["value"] = $v;
      }

      return $arginfo;
    }

    if ($argName === "??") {
      $help = preg_replace('/\s(bool|int|str)\s+/', " ", $argtext);
      return $help;
    }

    //  called with a question --> read argument value
    if ($argName === "")
      return $arginfo;
    if (isset($arginfo[$argName]["value"]))
      return $arginfo[$argName]["value"];
    return $default;
  }  
}

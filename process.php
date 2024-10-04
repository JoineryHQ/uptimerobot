#!/usr/bin/env php
<?php

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Command;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;

require_once __DIR__ . '/vendor/autoload.php';
require(__DIR__ . '/config.php');

define('NAME', 'Uptimerobot');
define('VERSION', '1.0-alpha');

$getOpt = new GetOpt();

// define common options
$getOpt->addOptions([
   
    Option::create(null, 'version', GetOpt::NO_ARGUMENT)
        ->setDescription('Show version information and quit'),
        
    Option::create('?', 'help', GetOpt::NO_ARGUMENT)
        ->setDescription('Show this help and quit'),
    
]);

// add simple commands
$getOpt->addCommand(Command::create('test-setup', function () { 
    echo 'When you see this message the setup works.' . PHP_EOL;
})->setDescription('Check if setup works'));

// add commands
$getOpt->addCommand(new Uptimerobot\Command\ListCommand());

    $uptimerobot = new Uptimerobot\UptimerobotApi(CONFIG['UPTIMROBOT_API']['KEY']);

//$getOpt->addCommand(new MoveCommand());
//$getOpt->addCommand(new DeleteCommand());


// process arguments and catch user errors
try {
    try {
        $getOpt->process();
    } catch (Missing $exception) {
        // catch missing exceptions if help is requested
        if (!$getOpt->getOption('help')) {
            throw $exception;
        }
    }
} catch (ArgumentException $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
    echo PHP_EOL . $getOpt->getHelpText();
    exit;
}

// show version and quit
if ($getOpt->getOption('version')) {
    echo sprintf('%s: %s' . PHP_EOL, NAME, VERSION);
    exit;
}

// show help and quit
$command = $getOpt->getCommand();
if (!$command || $getOpt->getOption('help')) {
    echo $getOpt->getHelpText();
    exit;
}

// call the requested command
call_user_func($command->getHandler(), $getOpt);

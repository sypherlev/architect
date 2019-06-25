<?php

if (php_sapi_name() != "cli") {
    die("Restricted to command line access only.");
}

$autoload = null;

$autoloadFiles = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php'
];

foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        $autoload = realpath($autoloadFile);
        break;
    }
}

if (! $autoload) {
    echo "Autoload file not found; try 'composer dump-autoload' first." . PHP_EOL;
    exit(1);
}

require $autoload;

$envFile = __DIR__ . '/../../../../';

$dotenv = new Dotenv\Dotenv($envFile);
$dotenv->load();

array_shift($argv);

$database_prefix = array_shift($argv);
$sourceGen = new \SypherLev\Architect\SourceGen();
$source = $sourceGen->generateSource($database_prefix);

$architect = new \SypherLev\Architect\Architect($source, $source->generateNewQuery());
$architect->build($argv, $database_prefix);
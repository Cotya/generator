#!/usr/bin/env php
<?php
foreach (array(__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../autoload.php',) as $file) {
    if (file_exists($file)) {
        require_once($file);
        break;
    }
}
unset($file);
use Cotya\Generator\Console;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new Console\MagentoModuleCreate());
$application->run();

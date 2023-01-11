<?php

use DualMedia\DoctrineEventConverterBundle\Tests\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__.'/../vendor/autoload.php';

$application = new Application(new TestKernel('test', true));
$application->setAutoExit(false);

$input = new ArrayInput(['command' => 'doctrine:database:drop', '--no-interaction' => true, '--force' => true, '--quiet' => true]);
$application->run($input, new ConsoleOutput());

$input = new ArrayInput(['command' => 'doctrine:database:create', '--no-interaction' => true]);
$application->run($input, new ConsoleOutput());

$input = new ArrayInput(['command' => 'doctrine:schema:create']);
$application->run($input, new ConsoleOutput());

$input = new ArrayInput(['command' => 'doctrine:fixtures:load', '--no-interaction' => true, '--append' => false]);
$application->run($input, new ConsoleOutput());

unset($input, $application);

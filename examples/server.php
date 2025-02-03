#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

set_time_limit(0);
ini_set('memory_limit', '-1');

use Bratikov\MQ\Agent\Http as HttpAgent;
use Bratikov\MQ\Factory;

$factory = new Factory();
$factory->addChannel('my_channel', 128);
$factory->setAgent(new HttpAgent('127.0.0.1', 3333));
$factory->handle();
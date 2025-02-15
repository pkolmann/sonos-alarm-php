#!/usr/bin/php
<?php
require_once __DIR__ . "/vendor/autoload.php";

use duncan3dc\Sonos\Devices\Discovery;
use duncan3dc\Sonos\Network;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

# First create a device collection that auto discovers devices from the network
$collection = new Discovery();
$collection->setNetworkInterface("eno1");

# Create a logger to stdout so we can see in the terminal what's going on
$logger = new Logger("sonos");
$handler = new StreamHandler("php://stdout", Logger::DEBUG);
//$logger->pushHandler($handler);

# Finally create your network instance using the cached collection
$sonos = new Network($collection);
$sonos->setLogger($logger);

# Controllers
echo "Controllers" . PHP_EOL;
$controllers = $sonos->getControllers();
foreach ($controllers as $controller) {
    echo "Room:      " . $controller->getRoom() . PHP_EOL;
    echo "Group:     " . $controller->getGroup() . PHP_EOL;
    echo "Ip:        " . $controller->getIp() . PHP_EOL;
    echo "Volume:    " . $controller->getVolume() . PHP_EOL;
    echo "Bass:      " . $controller->getBass() . PHP_EOL;
    echo "Treble:    " . $controller->getTreble() . PHP_EOL;
    echo "Loudness:  " . $controller->getLoudness() . PHP_EOL;
    echo "Indicator: " . $controller->getIndicator() . PHP_EOL;
    echo "Name:      " . $controller->getName() . PHP_EOL;
    echo "Uuid:      " . $controller->getUuid() . PHP_EOL;

    echo PHP_EOL;
    echo PHP_EOL;
}
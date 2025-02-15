#!/usr/bin/php
<?php
require_once __DIR__ . "/vendor/autoload.php";

use duncan3dc\Sonos\Devices\Discovery;
use duncan3dc\Sonos\Network;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

# First create a device collection that auto discovers devices from the network
$collection = new Discovery();

# Create a logger to stdout so we can see in the terminal what's going on
$logger = new Logger("sonos");
$handler = new StreamHandler("php://stdout", Logger::DEBUG);
//$logger->pushHandler($handler);

# Finally create your network instance using the cached collection
$sonos = new Network($collection);
$sonos->setLogger($logger);

# Get all devices
$speakers = $sonos->getSpeakers();
foreach ($speakers as $speaker) {
    echo "Room:        " . $speaker->getRoom() . PHP_EOL;
    echo "Group:       " . $speaker->getGroup() . PHP_EOL;
    echo "Ip:          " . $speaker->getIp() . PHP_EOL;
    echo "Volume:      " . $speaker->getVolume() . PHP_EOL;
    echo "Bass:        " . $speaker->getBass() . PHP_EOL;
    echo "Treble:      " . $speaker->getTreble() . PHP_EOL;
    echo "Loudness:    " . $speaker->getLoudness() . PHP_EOL;
    echo "Indicator:   " . $speaker->getIndicator() . PHP_EOL;
    echo "Name:        " . $speaker->getName() . PHP_EOL;
    echo "Uuid:        " . $speaker->getUuid() . PHP_EOL;
    echo "Coordinator: " . ($speaker->isCoordinator() ? "Is Coordinator" : "Is not Coordinator") . PHP_EOL;


    echo PHP_EOL;
    echo PHP_EOL;
}

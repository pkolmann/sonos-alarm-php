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

# Get all alarms
echo "Alarms" . PHP_EOL;
$alarms = $sonos->getAlarms();
foreach ($alarms as $alarm) {
    echo "Alarm- Id:            " . $alarm->getId() . PHP_EOL;
    echo "Time:                 " . $alarm->getTime() . PHP_EOL;
    echo "FrequencyDescription: " . $alarm->getFrequencyDescription() . PHP_EOL;
    echo "Frequency:            " .$alarm->getFrequency() . PHP_EOL;
    echo "Duration:             " . $alarm->getDuration() . PHP_EOL;
    echo "Enabled:              " . ($alarm->isActive() ? "Active" : "Dormant") . PHP_EOL;
    echo "Music:                " . $alarm->getMusic()->getMetaData() . PHP_EOL;
    echo "Repeat:               " .($alarm->getRepeat() ? "Repeat" : "No Repeat") . PHP_EOL;
    echo "Room:                 " . $alarm->getRoom() . PHP_EOL;
    echo "SpeakerName:          " . $alarm->getSpeaker()->getName() . PHP_EOL;
    echo "SpeakerRoom:          " . $alarm->getSpeaker()->getRoom() . PHP_EOL;
    echo "Volume:               " . $alarm->getVolume() . PHP_EOL;
    echo "Shuffle:              " . ($alarm->getShuffle() ? "Shuffle" : "No Shuffle") . PHP_EOL;

    echo PHP_EOL;
    echo PHP_EOL;
}
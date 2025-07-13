<?php

use duncan3dc\Sonos\Exceptions\NotFoundException;
use duncan3dc\Sonos\Network;

header('Content-Type: application/json');

function logger($message): void
{
    $appdir = dirname(__DIR__);
    $logfile = "$appdir/api.log";
    $log = new Monolog\Logger("sonos");
    $log->pushHandler(new Monolog\Handler\StreamHandler($logfile, Monolog\Logger::DEBUG));
    $log->debug($message);
}

$appdir = dirname(__DIR__);
require_once "$appdir/vendor/autoload.php";
require_once "$appdir/lib/SonosAlarm.php";

$sonos = new Network;
$sonosAlarm = new SonosAlarm();
$alarms = $sonosAlarm->getAlarms();

if (!isset($_GET['cmd'])) {
    $error = [
        "error" => "No command specified"
    ];

    print(json_encode($error));
    exit;
}

if ($_GET['cmd'] == "getAlarms") {
    $alarmsData = [];
    foreach ($alarms as $alarm) {
        try {
            $title = $sonosAlarm->getMusicTitle($alarm->getMusic());
        } catch (Exception $e) {
            $title = "Error: " . $e->getMessage();
        }

        $alarmsData[] = [
            "id" => $alarm->getId(),
            "time" => $alarm->getTime()->format("%H:%M:%S"),
            "room" => $alarm->getSpeaker()->getRoom(),
            "frequency" => $alarm->getFrequency(),
            "frequencyDescription" => $alarm->getFrequencyDescription(),
            "enabled" => $alarm->isActive(),
            "duration" => $alarm->getDuration()->format("%H:%M:%S"),
            "music" => $title,
            "repeat" => $alarm->getRepeat(),
            "volume" => $alarm->getVolume(),
            "shuffle" => $alarm->getShuffle(),
        ];
    }
    print(json_encode($alarmsData));
    exit;
}

if ($_GET['cmd'] == "getRooms") {
    $rooms = [];

    try {
        $speakers = $sonosAlarm->getSpeakers();
    } catch (UnknownGroupException $e) {
        $speakers = [];
    }
    foreach ($speakers as $speaker) {
        $rooms[] = [
            "name" => $speaker['room'],
            "uuid" => $speaker['uuid'],
            "ip" => $speaker['ip']
        ];
    }
    print(json_encode($rooms));
    exit;
}

if ($_GET['cmd'] == "toggleAlarm"&& isset($_GET['alarmId'])) {
    $alarmId = $_GET['alarmId'];
    $sonosAlarm->toggleAlarm($alarmId);
}

if (
    isset($_GET['cmd'])
    && $_GET['cmd'] == "addAlarm"
    && isset($_GET['room'])
    && isset($_GET['time'])
    && isset($_GET['frequency'])
) {
    $room = $_GET['room'];
    $time = $_GET['time'];
    $frequency = $_GET['frequency'];
    logger("Adding alarm in room: $room at time: $time with frequency: $frequency");

    try {
        // find speaker by room
        $speaker = $sonos->getSpeakerByRoom($room);
        logger("Found speaker: " . $speaker->getName() . " in room: $room");
        $newAlarm = $sonos->createAlarm($speaker);
        logger("Created alarm object for speaker: " . $speaker->getName());
        $newAlarm->setTime($time);
        logger("Setting alarm time: $time");
        $newAlarm->setFrequency($frequency);
        logger("Setting alarm frequency: $frequency");
        $newAlarm->setMusic("x-rincon-mp3radio://streaming.radio.co/sd0c4f2b1c/listen");
        logger("Setting alarm music to streaming.radio.co");
        $newAlarm->setDuration(600); // 10 minutes
        logger("Setting alarm duration to 600 seconds (10 minutes)");
        $newAlarm->setVolume(5); // 20% volume
        logger("Setting alarm volume to 5 (20%)");
        $newAlarm->setShuffle(false);
        logger("Setting alarm shuffle to false");
        $newAlarm->setRepeat(false);
        logger("Setting alarm repeat to false");
        logger("Setting alarm properties: time=$time, frequency=$frequency, music=streaming.radio.co, duration=600, volume=5, shuffle=false, repeat=false");
        $newAlarm->activate();
        logger("Alarm activated successfully in room: $room");

    } catch (NotFoundException $e) {
        $error = [
            "error" => "Speaker not found in room: $room. Please check the room name. (" . $e->getMessage() . ")"
        ];
        print(json_encode($error));
        logger("Error: " . $error['error']);
        exit;
    } catch (Exception $e) {
        $addAlarmRet = $sonosAlarm->addAlarm($room, $time, $frequency);
    } catch (Exception $e) {
        $error = [
            "error" => "An unknown error occurred while adding the alarm: " . $e->getMessage()
        ];
        print(json_encode($error));
        logger("Error: " . $error['error']);
        logger($e);
        exit;
    }
    print(json_encode($newAlarm));
}

if ($_GET['cmd'] == "deleteAlarm" && isset($_GET['alarmId'])) {
    $alarmId = $_GET['alarmId'];

    $alarms = $sonosAlarm->getAlarms();
    foreach ($alarms as $alarm) {
        if ($alarm->getId() == $alarmId) {
            $alarm->delete();
            print(json_encode(["success" => "Alarm deleted"]));
            exit;
        }
    }
    print(json_encode(["error" => "Alarm not found!"]));
}
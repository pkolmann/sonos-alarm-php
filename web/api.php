<?php

use duncan3dc\Sonos\Exceptions\UnknownGroupException;

header('Content-Type: application/json');

$appdir = dirname(__DIR__);
require_once "$appdir/vendor/autoload.php";
require_once "$appdir/lib/SonosAlarm.php";

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
    $_GET['cmd'] == "addAlarm"
    && isset($_GET['room'])
    && isset($_GET['time'])
    && isset($_GET['frequency'])
) {
    $room = $_GET['room'];
    $time = $_GET['time'];
    $frequency = $_GET['frequency'];

    try {
        $addAlarmRet = $sonosAlarm->addAlarm($room, $time, $frequency);
    } catch (Exception $e) {
        $error = [
            "error" => $e->getMessage()
        ];
        print(json_encode($error));
        exit;
    }
    print(json_encode($addAlarmRet));
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
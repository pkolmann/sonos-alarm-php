<?php

use duncan3dc\Sonos\Exceptions\NotFoundException;
use duncan3dc\Sonos\Network;
use duncan3dc\Sonos\Uri;
use duncan3dc\Sonos\Utils\Time;

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

    // sort alarms by time and room
    usort($alarmsData, function ($a, $b) {
        if ($a['time'] == $b['time']) {
            return strcmp($a['room'], $b['room']);
        }
        return strcmp($a['time'], $b['time']);
    });

    print(json_encode($alarmsData));
    exit;
}

if ($_GET['cmd'] == "getAlarmDetails") {
    $details = [];
    $details['rooms'] = [];
    $details['music'] = [];

    try {
        $speakers = $sonosAlarm->getSpeakers();
    } catch (Exception $e) {
        $speakers = [];
    }
    foreach ($speakers as $speaker) {
        $details['rooms'][] = [
            "name" => $speaker['room'],
            "uuid" => $speaker['uuid'],
            "ip" => $speaker['ip']
        ];
    }
    foreach ($alarms as $alarm) {
        try {
            // if uri is already in details, skip it
            if (in_array($alarm->getMusic()->getUri(), array_column($details['music'], 'uri'))) {
                continue;
            }

            $details['music'][] = [
                "title" => $sonosAlarm->getMusicTitle($alarm->getMusic()),
                "uri" => $alarm->getMusic()->getUri()
            ];
        } catch (Exception $e) {
            // If there is an error getting the music title, ignore it
        }
    }
    print(json_encode($details));
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
    $music = $_GET['music'] ?? null;
    $frequency = $_GET['frequency'];
    $duration = $_GET['duration'] ?? 600;
    logger("Adding alarm in room: $room at time: $time with frequency: $frequency and music: $music for duration: $duration seconds");

    // Validate room
    if (empty($room)) {
        $error = [
            "error" => "Room is required"
        ];
        print(json_encode($error));
        logger("Error: " . $error['error']);
        exit;
    }
    // Validate time
    if (empty($time) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
        $error = [
            "error" => "Time must be in HH:MM format"
        ];
        print(json_encode($error));
        logger("Error: " . $error['error']);
        exit;
    }
    // Validate frequency
    if (!is_numeric($frequency)) {
        $error = [
            "error" => "Frequency must be a numeric value"
        ];
        print(json_encode($error));
        logger("Error: " . $error['error']);
        exit;
    }

    // Validate music
    $musicMetadata = null;
    if (empty($music)) {
        $error = [
            "error" => "Music URI is required"
        ];
        print(json_encode($error));
        logger("Error: " . $error['error']);
        exit;
    } else {
        // check if music url is in $alarms
        foreach ($alarms as $alarm) {
            if ($alarm->getMusic()->getUri() == $music) {
                $musicMetadata = $alarm->getMusic()->getMetaData();
                break;
            }
        }
        if (empty($musicMetadata)) {
            $error = [
                "error" => "Music URI not found in existing alarms"
            ];
            print(json_encode($error));
            logger("Error: " . $error['error']);
            exit;
        }
    }

    try {
        // find speaker by room
        $speaker = $sonos->getSpeakerByRoom($room);
        $newAlarm = $sonos->createAlarm($speaker);
        $newAlarm->setTime(Time::parse("$time:00")); // Set time in HH:MM:SS format
        $newAlarm->setFrequency($frequency);
        $newAlarm->setMusic(new Uri($music, $musicMetadata));
        $newAlarm->setDuration(Time::parse($duration)); // 10 minutes
        $newAlarm->setVolume(5); // 5% volume
        $newAlarm->setShuffle(false);
        $newAlarm->setRepeat(false);
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
        $error = [
            "error" => "An unknown error occurred while adding the alarm (line: {$e->getFile()}:{$e->getLine()}: " . $e->getMessage()
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
<?php

use duncan3dc\Sonos\Exceptions\UnknownGroupException;

header('Content-Type: application/json');

$appdir = dirname(__DIR__);
require_once "$appdir/vendor/autoload.php";
require_once "$appdir/lib/SonosAlarm.php";

$sonosAlarm = new SonosAlarm();
$alarms = $sonosAlarm->getAlarms();

if (
    isset($_GET['cmd'])
    && $_GET['cmd'] == "toggleAlarm"
    && isset($_GET['alarmId'])
) {
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

    try {
        $addAlarmRet = $sonosAlarm->addAlarm($room, $time, $frequency);
    } catch (UnknownGroupException $e) {
        $error = [
            "error" => $e->getMessage()
        ];
        print(json_encode($error));
        exit;
    }
    print(json_encode($addAlarmRet));
}
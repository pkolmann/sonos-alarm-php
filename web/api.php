<?php

$appdir = dirname(__DIR__);
require_once "$appdir/vendor/autoload.php";
require_once "$appdir/lib/SonosAlarm.php";

$sonosAlarm = new SonosAlarm();
$alarms = $sonosAlarm->getAlarms();

if (isset($_GET['alarmId'])) {
    $alarmId = $_GET['alarmId'];
    $sonosAlarm->toggleAlarm($alarmId);
}
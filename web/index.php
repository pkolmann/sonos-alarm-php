<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sonos Wecker</title>
    <link rel="stylesheet" href="style.css">
    <!-- include bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        function showSection(section) {
            const sections = document.getElementsByClassName("content-section");
            for (let i = 0; i < sections.length; i++) {
                sections[i].classList.remove("active-section");
            }
            document.getElementById(section).classList.add("active-section");
        }

        function toggleAlarm(alarmId) {
            console.log("Toggle alarm: " + alarmId);

            // activate the loader
            document.getElementById("loader")['style'].display = "block";

            fetch("api.php?cmd=toggleAlarm&alarmId=" + alarmId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.text();
                })
                .then(data => {
                    console.log(data);

                    // Update the UI
                    const alarmEnabled = document.getElementById("alarm-" + alarmId + "-enabled");
                    const alarmIcon = document.getElementById("alarm-" + alarmId + "-icon");
                    if (data === "active") {
                        console.log("updating to active");
                        alarmEnabled.innerHTML = "<b>Enabled:</b> Active now (timestamp: " + new Date().toLocaleTimeString() + ")";
                        alarmIcon.innerHTML = "⏰";

                        // reset the loader
                        document.getElementById("loader")['style'].display = "none";
                    } else {
                        console.log("updating to dormant");
                        alarmEnabled.innerHTML = "<b>Enabled:</b> Dormant now (timestamp: " + new Date().toLocaleTimeString() + ")";
                        alarmIcon.innerHTML = "😴";

                        // reset the loader
                        document.getElementById("loader")['style'].display = "none";
                    }
                })
                .catch(error => {
                    console.error("There has been a problem with your fetch operation:", error);

                    // reset the loader
                    document.getElementById("loader")['style'].display = "none";
                });
            return true;
        }

        function editAlarm(alarmId) {
            console.log("Edit alarm: " + alarmId);
            return true;
        }

        function addAlarm() {
            const room = document.getElementById("room").valueOf().value;
            const time = document.getElementById("time").valueOf().value;
            const frequency = document.getElementById("frequency").valueOf().value;

            console.log("Add alarm: " + room + " " + time + " " + frequency);

            // activate the loader
            document.getElementById("loader")['style'].display = "block";

            fetch("api.php?cmd=addAlarm&room=" + room + "&time=" + time + "&frequency=" + frequency)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.text();
                })
                .then(data => {
                    console.log(data);

                    // reset the loader
                    document.getElementById("loader")['style'].display = "none";
                })
                .catch(error => {
                    console.error("There has been a problem with your fetch operation:", error);

                    // reset the loader
                    document.getElementById("loader")['style'].display = "none";
                });

            return true;
        }
    </script>
</head>
<body class="py-4">
<div id="loader" class="loader"></div>
<div class="container">
<h1>Sonos Wecker</h1>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" href="#" onclick="showSection('page1')">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="showSection('page2')">Add Alarm</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <div id="page1" class="content-section active-section">
<?php

use duncan3dc\Sonos\Exceptions\UnknownGroupException;

$appdir = dirname(__DIR__);
require_once "$appdir/vendor/autoload.php";
require_once "$appdir/lib/SonosAlarm.php";

$sonosAlarm = new SonosAlarm();
$alarms = $sonosAlarm->getAlarms();

$count = 0;
echo "<div class = 'row'>";
foreach ($alarms as $alarm) {
    print "<pre>";
    print_r($alarm);
    print "</pre>";

    if ($count % 3 == 0) {
        echo "</div>" . PHP_EOL;
        echo "<div class = 'row mt-4'>" . PHP_EOL;
    }
    $count++;

    $alarmId = $alarm->getId();
    echo "<div class='col-sm-4'>" . PHP_EOL;
    echo "<div class='card position-relative'>" . PHP_EOL;
    echo "<div id='alarm-$alarmId-icon' class='card-icon' onclick='toggleAlarm($alarmId)'>" . ($alarm->isActive() ? "⏰" : "😴") . "</div>" . PHP_EOL;
    echo "<div class='card-body'>" . PHP_EOL;
    echo "<h5 class='card-title'>Alarm</h5>" . PHP_EOL;

    echo "<p>{$alarm->getTime()}@{$alarm->getFrequencyDescription()}</p>" . PHP_EOL;

    echo "<ul>" . PHP_EOL;
    echo "<li><b>Count:</b> $count</li>" . PHP_EOL;
    echo "<li><b>Id:</b> $alarmId</li>" . PHP_EOL;
    echo "<li><b>Time:</b> {$alarm->getTime()}</li>" . PHP_EOL;
    echo "<li><b>FrequencyDescription:</b> {$alarm->getFrequencyDescription()}</li>" . PHP_EOL;
    echo "<li><b>Frequency:</b> {$alarm->getFrequency()}</li>" . PHP_EOL;
    echo "<li><b>Duration:</b> {$alarm->getDuration()}</li>" . PHP_EOL;
    echo "<li id='alarm-$alarmId-enabled'><b>Enabled:</b> " . ($alarm->isActive() ? "Active" : "Dormant") . "</li>" . PHP_EOL;
    try {
        $title = $sonosAlarm->getMusicTitle($alarm->getMusic());
    } catch (Exception $e) {
        $title = "Error: " . $e->getMessage();
    }
    echo "<li><b>Music:</b> $title</li>" . PHP_EOL;
    echo "<li><b>Repeat:</b> " . ($alarm->getRepeat() ? "Repeat" : "No Repeat") . "</li>" . PHP_EOL;
    echo "<li><b>Room:</b> {$alarm->getRoom()}</li>" . PHP_EOL;
    echo "<li><b>SpeakerName:</b> {$alarm->getSpeaker()->getName()}</li>" . PHP_EOL;
    echo "<li><b>SpeakerRoom:</b> {$alarm->getSpeaker()->getRoom()}</li>" . PHP_EOL;
    echo "<li><b>Volume:</b> {$alarm->getVolume()}</li>" . PHP_EOL;
    echo "<li><b>Shuffle:</b> " . ($alarm->getShuffle() ? "Shuffle" : "No Shuffle") . "</li>" . PHP_EOL;
    echo "</ul>" . PHP_EOL;

    echo "<button type='button' class='btn btn-primary' onclick='toggleAlarm($alarmId)'>Toggle</button>" . PHP_EOL;
    echo "<button type='button' class='btn btn-primary' onclick='editAlarm($alarmId)'>Edit</button>" . PHP_EOL;

    echo "</div>" . PHP_EOL; // card-body
    echo "</div>" . PHP_EOL; // card
    echo "</div>" . PHP_EOL; // col-sm-4
}
echo "</div>" . PHP_EOL; // row


?>
    </div>
    <div id="page2" class="content-section">
        <h2>Add Alarm</h2>
        <div class="form-group">
            <p>
                <label for="room">Room:</label>
                <select id="room" name="room" required>
                    <?php
                    try {
                        $speakers = $sonosAlarm->getSpeakers();
                    } catch (UnknownGroupException $e) {
                        $speakers = [];
                    }
                    print "<option value=''>Select a room</option>";
                        foreach ($speakers as $speaker) {
                            $room = $speaker['room'];
                            $roomId = $speaker['uuid'];
                            echo "<option value='$roomId'>$room</option>" . PHP_EOL;
                        }
                    ?>
                </select>
            </p>
            <p>
                <label for="time">Time:</label>
                <input id="time" type="time" name="time" required>
            </p>
            <p>
                <label for="frequency">Frequency:</label>
                <select id="frequency" name="frequency" required>
                    <option value="">Select a frequency</option>
                    <option value=127>Daily</option>
                    <option value=31>Weekdays</option>
                    <option value=96>Weekends</option>
                    <optgroup label="Weekdays">
                        <option value=1>Monday</option>
                        <option value=2>Tuesday</option>
                        <option value=4>Wednesday</option>
                        <option value=8>Thursday</option>
                        <option value=16>Friday</option>
                        <option value=32>Saturday</option>
                        <option value=64>Sunday</option>
                    </optgroup>
                </select>
            </p>

            <button class="btn btn-primary" onclick="addAlarm()">Add Alarm</button>
        </div>
    </div>
</div>
</div>
</body>
</html>
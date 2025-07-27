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
    <script src="sonos-alarm.js"></script>
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
          <a class="nav-link active" href="#" onclick="showSection('home')">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="showSection('add')">Add Alarm</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
    <div class="error-message" id="error-message"></div>

<div class="container mt-4">
    <div id="home" class="content-section active-section">
        <div class="row" id="alarm-list">
        </div></div>
    <div id="add" class="content-section">
        <h2>Add Alarm</h2>
        <div class="form-group">
            <p>
                <label for="room">Room:</label>
                <select id="room" name="room" required></select>
            </p>
            <p>
                <label for="time">Time:</label>
                <input id="time" type="time" name="time" required>
            </p>
            <p>
                <label for="music">Music:</label>
                <select id="music" name="music" required></select>
            </p>
            <p>
                <label for="frequency">Frequency:</label>
                <select id="frequency" name="frequency" required>
                    <option value="">Select a frequency</option>
                    <option value="0">Once</option>
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